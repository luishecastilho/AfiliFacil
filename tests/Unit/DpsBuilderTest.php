<?php

namespace Tests\Unit;

use App\DTOs\InvoicePayloadDTO;
use App\DTOs\SellerDTO;
use App\Models\Issuer;
use App\Nfse\Dps\DpsBuilder;
use DOMDocument;
use Tests\TestCase;

class DpsBuilderTest extends TestCase
{
    private function issuer(): Issuer
    {
        $issuer = new Issuer;
        $issuer->forceFill([
            'id' => 1,
            'tax_document' => '11222333000181',
            'document_type' => 'cnpj',
            'inscricao_municipal' => '123456',
            'address_ibge_code' => '3550308',
            'regime_tributario' => 'simples_nacional',
            'service_code' => '10.05',
            'iss_rate' => 2.5,
            'iss_withheld' => false,
            'ambiente' => 'producao_restrita',
        ]);

        return $issuer;
    }

    private function payload(): InvoicePayloadDTO
    {
        return new InvoicePayloadDTO(
            invoiceId: 1,
            referenceMonth: '2026-07',
            amount: 1000.0,
            seller: new SellerDTO(
                taxDocument: '99999999000191',
                documentType: 'cnpj',
                name: 'Tomador LTDA',
                addressIbgeCode: '3550308',
            ),
            issuerId: 1,
            dpsSerie: '00001',
            dpsNumero: 5,
        );
    }

    public function test_dps_id_has_expected_shape(): void
    {
        $id = (new DpsBuilder('Test-1.0'))->dpsId($this->issuer(), $this->payload());

        $this->assertSame(45, strlen($id));
        $this->assertStringStartsWith('DPS3550308', $id);
        $this->assertStringContainsString('11222333000181', $id);
        $this->assertStringEndsWith('000000000000005', $id); // nDPS padded to 15
    }

    public function test_build_produces_valid_xml_with_key_fields(): void
    {
        $doc = (new DpsBuilder('Test-1.0'))->build($this->issuer(), $this->payload());
        $xml = $doc->saveXML();

        $this->assertTrue((new DOMDocument)->loadXML($xml));
        $this->assertStringContainsString('<infDPS Id="DPS3550308', $xml);
        $this->assertStringContainsString('<CNPJ>11222333000181</CNPJ>', $xml);
        $this->assertStringContainsString('<CNPJ>99999999000191</CNPJ>', $xml); // tomador
        $this->assertStringContainsString('<vServ>1000.00</vServ>', $xml);
        $this->assertStringContainsString('<cTribNac>10.05</cTribNac>', $xml);
    }
}
