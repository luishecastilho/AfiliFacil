<?php

namespace App\Actions\Import;

use App\Models\Import;

class DetectDuplicateImportAction
{
    public function handle(int $userId, string $fileHash): ?Import
    {
        return Import::where('user_id', $userId)
            ->where('file_hash', $fileHash)
            ->first();
    }
}
