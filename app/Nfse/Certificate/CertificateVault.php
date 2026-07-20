<?php

namespace App\Nfse\Certificate;

use App\Exceptions\CertificateException;
use App\Models\Issuer;
use App\Services\StorageService;
use App\Support\TaxDocument;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * Encrypted-at-rest vault for A1 certificates.
 *
 * Security invariants:
 * - PFX bytes are encrypted (Crypt) before being written to S3; the password is
 *   stored via the model's `encrypted` cast.
 * - Decrypted material lives only in memory (or in 0600 temp files that are always
 *   unlinked) and is never logged or placed on a serialized job.
 * - There is no method that returns the raw PFX to a caller/endpoint.
 */
class CertificateVault
{
    private const DISK = 's3';

    public function __construct(
        private readonly StorageService $storage,
        private readonly CertificateReader $reader,
    ) {}

    /**
     * Validate and store a new certificate for the issuer (overwrites any existing one).
     *
     * @throws CertificateException
     */
    public function store(Issuer $issuer, string $pfxBytes, string $password): void
    {
        $material = $this->reader->read($pfxBytes, $password);

        if ($material->isExpired()) {
            throw CertificateException::expired();
        }

        if (! $this->documentMatches($issuer, $material)) {
            throw CertificateException::documentMismatch();
        }

        $previousPath = $issuer->certificate_path;

        $path = "certificates/{$issuer->user_id}/".Str::uuid()->toString().'.pfx.enc';
        $this->storage->put(self::DISK, $path, Crypt::encryptString($pfxBytes));

        $issuer->forceFill([
            'certificate_path' => $path,
            'certificate_password' => $password,
            'certificate_subject_cn' => $material->subjectCn,
            'certificate_document' => $material->document,
            'certificate_valid_from' => $material->validFrom,
            'certificate_valid_until' => $material->validUntil,
            'portal_validated_at' => null, // certificate changed → require re-validation
        ])->save();

        if ($previousPath && $previousPath !== $path) {
            $this->storage->delete(self::DISK, $previousPath);
        }
    }

    /**
     * Load and decrypt the certificate material into memory.
     *
     * @throws CertificateException
     */
    public function readMaterial(Issuer $issuer): CertificateMaterial
    {
        if (! $issuer->certificate_path) {
            throw CertificateException::notFound();
        }

        $encrypted = $this->storage->get(self::DISK, $issuer->certificate_path);

        if ($encrypted === null) {
            throw CertificateException::notFound();
        }

        return $this->reader->read(
            Crypt::decryptString($encrypted),
            (string) $issuer->certificate_password,
        );
    }

    /**
     * Materialize cert + key as 0600 temp PEM files (required by cURL for mTLS),
     * run the callback, and always unlink them afterwards.
     *
     * @template T
     *
     * @param  callable(string $certFile, string $keyFile, CertificateMaterial $material): T  $callback
     * @return T
     *
     * @throws CertificateException
     */
    public function withTempPem(Issuer $issuer, callable $callback): mixed
    {
        $material = $this->readMaterial($issuer);

        $certFile = tempnam(sys_get_temp_dir(), 'nfse_cert_');
        $keyFile = tempnam(sys_get_temp_dir(), 'nfse_key_');

        try {
            chmod($certFile, 0600);
            chmod($keyFile, 0600);
            file_put_contents($certFile, $material->certificatePem);
            file_put_contents($keyFile, $material->privateKeyPem);

            return $callback($certFile, $keyFile, $material);
        } finally {
            @unlink($certFile);
            @unlink($keyFile);
        }
    }

    private function documentMatches(Issuer $issuer, CertificateMaterial $material): bool
    {
        if ($material->document === null) {
            return false;
        }

        return TaxDocument::sanitize($material->document) === TaxDocument::sanitize($issuer->tax_document);
    }
}
