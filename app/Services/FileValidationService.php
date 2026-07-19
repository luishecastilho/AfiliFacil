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

    public function validate(UploadedFile $file): bool
    {
        $mimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file->getRealPath());

        return in_array($mimeType, self::ALLOWED_MIME_TYPES, true);
    }
}
