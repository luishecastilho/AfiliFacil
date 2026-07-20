<?php

namespace Tests\Unit;

use App\DTOs\InvoicePayloadDTO;
use App\DTOs\SellerDTO;
use App\Models\Issuer;
use App\Nfse\Certificate\CertificateReader;
use App\Nfse\Dps\DpsBuilder;
use App\Nfse\Dps\DpsSigner;
use DOMElement;
use Tests\Concerns\CreatesTestCertificate;
use Tests\TestCase;

class DpsSignerTest extends TestCase
{
    use CreatesTestCertificate;

    private const DSIG = 'http://www.w3.org/2000/09/xmldsig#';

    private function issuer(): Issuer
    {
        $issuer = new Issuer;
        $issuer->forceFill([
            'id' => 1,
            'tax_document' => '11222333000181',
            'document_type' => 'cnpj',
            'address_ibge_code' => '3550308',
            'regime_tributario' => 'simples_nacional',
            'service_code' => '10.05',
            'iss_rate' => 2.5,
            'ambiente' => 'producao_restrita',
        ]);

        return $issuer;
    }

    public function test_signature_digest_and_value_verify(): void
    {
        $password = 'secret-pass';
        $pfx = $this->makePfx('EMPRESA TESTE:11222333000181', $password);
        $material = (new CertificateReader)->read($pfx, $password);

        $payload = new InvoicePayloadDTO(
            invoiceId: 1,
            referenceMonth: '2026-07',
            amount: 1000.0,
            seller: new SellerDTO(taxDocument: '99999999000191', documentType: 'cnpj', name: 'Tomador'),
            issuerId: 1,
            dpsSerie: '00001',
            dpsNumero: 1,
        );

        $doc = (new DpsBuilder('Test-1.0'))->build($this->issuer(), $payload);
        (new DpsSigner)->sign($doc, $material);

        // Signature exists.
        $signatures = $doc->getElementsByTagNameNS(self::DSIG, 'Signature');
        $this->assertSame(1, $signatures->length);

        // DigestValue matches the canonicalized infDPS.
        $inf = $doc->getElementsByTagName('infDPS')->item(0);
        $expectedDigest = base64_encode(hash('sha256', $inf->C14N(), true));
        $digestValue = $doc->getElementsByTagNameNS(self::DSIG, 'DigestValue')->item(0)->nodeValue;
        $this->assertSame($expectedDigest, $digestValue);

        // SignatureValue verifies against the certificate public key.
        /** @var DOMElement $signedInfo */
        $signedInfo = $doc->getElementsByTagNameNS(self::DSIG, 'SignedInfo')->item(0);
        $signatureValue = base64_decode($doc->getElementsByTagNameNS(self::DSIG, 'SignatureValue')->item(0)->nodeValue);
        $publicKey = openssl_pkey_get_public($material->certificatePem);

        $this->assertSame(
            1,
            openssl_verify($signedInfo->C14N(), $signatureValue, $publicKey, OPENSSL_ALGO_SHA256),
        );
    }
}
