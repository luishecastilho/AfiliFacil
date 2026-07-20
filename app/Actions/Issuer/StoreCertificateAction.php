<?php

namespace App\Actions\Issuer;

use App\Exceptions\CertificateException;
use App\Models\Issuer;
use App\Nfse\Certificate\CertificateVault;
use App\Services\FileValidationService;
use Illuminate\Http\UploadedFile;

class StoreCertificateAction
{
    public function __construct(
        private readonly CertificateVault $vault,
        private readonly FileValidationService $fileValidation,
    ) {}

    /**
     * @throws CertificateException
     */
    public function handle(Issuer $issuer, UploadedFile $file, string $password): void
    {
        if (! $this->fileValidation->validateCertificate($file)) {
            throw new CertificateException('O arquivo enviado não é um certificado A1 (.pfx/.p12) válido.');
        }

        $this->vault->store($issuer, (string) $file->get(), $password);
    }
}
