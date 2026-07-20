<?php

namespace App\Actions\Invoice;

use App\DTOs\InvoicePayloadDTO;
use App\DTOs\SellerDTO;
use App\Enums\InvoiceEventType;
use App\Enums\InvoiceStatus;
use App\Exceptions\InvoiceProviderException;
use App\InvoiceProvider\Contracts\InvoiceProviderInterface;
use App\Models\Invoice;
use App\Models\Issuer;
use Illuminate\Support\Facades\DB;

class IssueInvoiceAction
{
    public function __construct(private readonly InvoiceProviderInterface $provider) {}

    public function handle(Invoice $invoice): Invoice
    {
        $issuer = $invoice->import->user->issuer;

        if (! $issuer) {
            return $this->fail($invoice, 'Emitente (cadastro fiscal) não encontrado.');
        }

        if ($issuer->emission_mode->requiresCertificate() && ! $issuer->hasValidCertificate()) {
            return $this->fail($invoice, 'Certificado digital ausente ou expirado.');
        }

        $invoice->update(['status' => InvoiceStatus::Processing]);
        $invoice->events()->create(['event' => InvoiceEventType::Processing]);

        [$serie, $numero] = $this->allocateDpsNumber($issuer);

        $seller = $invoice->seller;
        $payload = new InvoicePayloadDTO(
            invoiceId: $invoice->id,
            referenceMonth: $invoice->reference_month,
            amount: (float) $invoice->amount,
            seller: new SellerDTO(
                taxDocument: $seller->tax_document,
                documentType: $seller->document_type,
                name: $seller->name,
                tradeName: $seller->trade_name,
                email: $seller->email,
                addressStreet: $seller->address_street,
                addressNumber: $seller->address_number,
                addressComplement: $seller->address_complement,
                addressDistrict: $seller->address_district,
                addressCity: $seller->address_city,
                addressState: $seller->address_state,
                addressZip: $seller->address_zip,
                addressIbgeCode: $seller->address_ibge_code,
            ),
            issuerId: $issuer->id,
            dpsSerie: $serie,
            dpsNumero: $numero,
        );

        try {
            $result = $this->provider->issue($payload);
        } catch (InvoiceProviderException $exception) {
            $invoice->increment('retry_count');
            $invoice->update(['status' => InvoiceStatus::Failed]);
            $invoice->events()->create([
                'event' => InvoiceEventType::Failed,
                'metadata' => ['error' => $exception->getMessage()],
            ]);

            throw $exception;
        }

        $issRate = $issuer->iss_rate !== null ? (float) $issuer->iss_rate : null;

        $invoice->update([
            'status' => InvoiceStatus::Generated,
            'invoice_number' => $result['invoice_number'],
            'access_key' => $result['access_key'],
            'issued_at' => now(),
            'issuer_id' => $issuer->id,
            'dps_serie' => $serie,
            'dps_numero' => $numero,
            'service_code' => $issuer->service_code,
            'iss_rate' => $issRate,
            'iss_amount' => $issRate !== null ? round((float) $invoice->amount * $issRate / 100, 2) : null,
            'ambiente' => $issuer->ambiente->value,
            'provider' => $this->provider->slug(),
            'provider_reference' => $result['reference'],
            'provider_payload' => $result['raw'],
        ]);

        $invoice->importRows()->update(['status' => 'invoiced']);
        $invoice->events()->create(['event' => InvoiceEventType::Generated]);

        return $invoice->fresh();
    }

    /**
     * Allocate the next DPS number for the issuer under a row lock (serialized per issuer)
     * to avoid duplicate/gap rejections (see .ai/nfse/pesquisa.md §12).
     *
     * @return array{0: string, 1: int}
     */
    private function allocateDpsNumber(Issuer $issuer): array
    {
        return DB::transaction(function () use ($issuer) {
            /** @var Issuer $locked */
            $locked = Issuer::withoutGlobalScopes()->whereKey($issuer->getKey())->lockForUpdate()->firstOrFail();

            $numero = (int) $locked->dps_proximo_numero;
            $locked->forceFill(['dps_proximo_numero' => $numero + 1])->save();

            return [$locked->dps_serie, $numero];
        });
    }

    private function fail(Invoice $invoice, string $message): Invoice
    {
        $invoice->update(['status' => InvoiceStatus::Failed]);
        $invoice->events()->create([
            'event' => InvoiceEventType::Failed,
            'metadata' => ['error' => $message],
        ]);

        return $invoice;
    }
}
