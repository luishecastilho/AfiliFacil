<?php

namespace Tests\Feature;

use App\DTOs\InvoicePayloadDTO;
use App\DTOs\SellerDTO;
use App\Exceptions\InvoiceProviderException;
use App\InvoiceProvider\Contracts\InvoiceProviderInterface;
use App\InvoiceProvider\Providers\NacionalNfseProvider;
use App\Models\Issuer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesTestCertificate;
use Tests\TestCase;

class NacionalNfseProviderTest extends TestCase
{
    use CreatesTestCertificate, RefreshDatabase;

    private function payload(Issuer $issuer): InvoicePayloadDTO
    {
        return new InvoicePayloadDTO(
            invoiceId: 1,
            referenceMonth: '2026-07',
            amount: 1000.0,
            seller: new SellerDTO(taxDocument: '99999999000191', documentType: 'cnpj', name: 'Tomador LTDA', addressIbgeCode: '3550308'),
            issuerId: $issuer->id,
            dpsSerie: '00001',
            dpsNumero: 1,
        );
    }

    private function makeIssuerWithCertificate(): Issuer
    {
        Storage::fake('s3');
        config(['afilifacil.invoice.driver' => 'nacional']);

        $issuer = Issuer::factory()->for(User::factory())->create();

        return $this->attachCertificate($issuer);
    }

    public function test_driver_binding_resolves_national_provider(): void
    {
        config(['afilifacil.invoice.driver' => 'nacional']);

        $this->assertInstanceOf(NacionalNfseProvider::class, app(InvoiceProviderInterface::class));
    }

    public function test_successful_issue_returns_key_and_xml(): void
    {
        $issuer = $this->makeIssuerWithCertificate();

        Http::fake([
            'sefin.producaorestrita.nfse.gov.br/*' => Http::response([
                'chaveAcesso' => str_repeat('1', 50),
                'nNFSe' => '123',
                'nfseXmlGZipB64' => base64_encode((string) gzencode('<NFSe>ok</NFSe>')),
            ], 201),
        ]);

        $result = app(InvoiceProviderInterface::class)->issue($this->payload($issuer));

        $this->assertSame(str_repeat('1', 50), $result['access_key']);
        $this->assertSame('123', $result['invoice_number']);
        $this->assertSame('<NFSe>ok</NFSe>', $result['raw']['nfse_xml']);
        $this->assertStringStartsWith('DPS', $result['reference']);
    }

    public function test_rejection_throws_provider_exception(): void
    {
        $issuer = $this->makeIssuerWithCertificate();

        Http::fake([
            'sefin.producaorestrita.nfse.gov.br/*' => Http::response([
                'erros' => [['descricao' => 'E0004 Identificador da DPS inválido']],
            ], 422),
        ]);

        $this->expectException(InvoiceProviderException::class);
        $this->expectExceptionMessageMatches('/E0004/');

        app(InvoiceProviderInterface::class)->issue($this->payload($issuer));
    }
}
