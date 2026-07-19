<?php

namespace App\Actions\Invoice;

use App\DTOs\InvoicePayloadDTO;
use App\DTOs\SellerDTO;
use App\Enums\InvoiceEventType;
use App\Enums\InvoiceStatus;
use App\Exceptions\InvoiceProviderException;
use App\InvoiceProvider\Contracts\InvoiceProviderInterface;
use App\Models\Invoice;

class IssueInvoiceAction
{
    public function __construct(private readonly InvoiceProviderInterface $provider)
    {
    }

    public function handle(Invoice $invoice): Invoice
    {
        $invoice->update(['status' => InvoiceStatus::Processing]);
        $invoice->events()->create(['event' => InvoiceEventType::Processing]);

        $seller = $invoice->seller;
        $issuer = $invoice->import->user->only(['name', 'email']);

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
            issuer: $issuer,
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

        $invoice->update([
            'status' => InvoiceStatus::Generated,
            'invoice_number' => $result['invoice_number'],
            'access_key' => $result['access_key'],
            'issued_at' => now(),
            'provider' => $this->provider->slug(),
            'provider_reference' => $result['reference'],
            'provider_payload' => $result['raw'],
        ]);

        $invoice->importRows()->update(['status' => 'invoiced']);
        $invoice->events()->create(['event' => InvoiceEventType::Generated]);

        return $invoice->fresh();
    }
}
