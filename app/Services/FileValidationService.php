<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class FileValidationService
{
    private const ALLOWED_MIME_TYPES = [
        'text/csv',
        'text/plain',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    private const ALLOWED_CERTIFICATE_MIME_TYPES = [
        'application/x-pkcs12',
        'application/pkcs12',
        'application/octet-stream',
    ];

    public function validate(UploadedFile $file): bool
    {
        $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file->getRealPath());

        return in_array($mimeType, self::ALLOWED_MIME_TYPES, true);
    }

    /**
     * Coarse MIME gate for A1 certificates (.pfx/.p12). Authoritative validation
     * is the PKCS#12 parse in App\Nfse\Certificate\CertificateReader.
     */
    public function validateCertificate(UploadedFile $file): bool
    {
        $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file->getRealPath());

        return in_array($mimeType, self::ALLOWED_CERTIFICATE_MIME_TYPES, true);
    }
}
