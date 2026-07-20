<?php

namespace Tests\Concerns;

use App\Models\Issuer;
use App\Nfse\Certificate\CertificateVault;
use App\Support\TaxDocument;

/**
 * Generates a self-signed A1-like PKCS#12 for tests and stores it in the vault.
 */
trait CreatesTestCertificate
{
    protected function attachCertificate(Issuer $issuer, string $password = 'secret-pass'): Issuer
    {
        $cnpj = TaxDocument::sanitize($issuer->tax_document);
        $pfx = $this->makePfx('EMPRESA TESTE:'.$cnpj, $password);

        app(CertificateVault::class)->store($issuer, $pfx, $password);

        return $issuer->refresh();
    }

    protected function makePfx(string $cn, string $password, int $daysValid = 365): string
    {
        $config = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];

        $pkey = @openssl_pkey_new($config);
        if ($pkey === false) {
            $this->markTestSkipped('OpenSSL key generation unavailable in this environment.');
        }

        $csr = @openssl_csr_new(['commonName' => $cn], $pkey, $config);
        if ($csr === false) {
            $this->markTestSkipped('OpenSSL CSR generation unavailable in this environment.');
        }

        $x509 = @openssl_csr_sign($csr, null, $pkey, $daysValid, $config);
        $pfx = '';
        @openssl_pkcs12_export($x509, $pfx, $pkey, $password);

        if ($pfx === '') {
            $this->markTestSkipped('OpenSSL PKCS#12 export unavailable in this environment.');
        }

        return $pfx;
    }
}
