<?php

namespace App\Nfse\Certificate;

use App\Exceptions\CertificateException;
use App\Support\TaxDocument;
use Carbon\CarbonImmutable;

/**
 * Parses an A1 (PKCS#12/.pfx) certificate into in-memory PEM material.
 * Pure: no disk I/O, no persistence.
 */
class CertificateReader
{
    /**
     * @throws CertificateException when the password is wrong or the file is not a valid PKCS#12
     */
    public function read(string $pfxBytes, string $password): CertificateMaterial
    {
        $certs = [];

        if (! openssl_pkcs12_read($pfxBytes, $certs, $password)) {
            throw CertificateException::invalidPassword();
        }

        $parsed = openssl_x509_parse($certs['cert']) ?: [];

        return new CertificateMaterial(
            certificatePem: $certs['cert'],
            privateKeyPem: $certs['pkey'],
            chainPem: $certs['extracerts'] ?? [],
            subjectCn: $parsed['subject']['CN'] ?? null,
            document: $this->extractDocument($parsed),
            validFrom: isset($parsed['validFrom_time_t'])
                ? CarbonImmutable::createFromTimestamp($parsed['validFrom_time_t'])
                : null,
            validUntil: isset($parsed['validTo_time_t'])
                ? CarbonImmutable::createFromTimestamp($parsed['validTo_time_t'])
                : null,
        );
    }

    /**
     * ICP-Brasil e-CNPJ/e-CPF store the document in the subject CN as "NAME:NUMBER".
     * Fallback: first 14- or 11-digit run found in the subject CN.
     *
     * @param  array<string, mixed>  $parsed
     */
    private function extractDocument(array $parsed): ?string
    {
        $cn = $parsed['subject']['CN'] ?? '';

        if (str_contains($cn, ':')) {
            $candidate = TaxDocument::sanitize((string) substr(strrchr($cn, ':'), 1));
            if (in_array(strlen($candidate), [11, 14], true)) {
                return $candidate;
            }
        }

        if (preg_match('/\d{14}|\d{11}/', TaxDocument::sanitize($cn), $matches)) {
            return $matches[0];
        }

        return null;
    }
}
