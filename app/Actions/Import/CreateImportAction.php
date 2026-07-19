<?php

namespace App\Actions\Import;

use App\Enums\ImportStatus;
use App\Events\Import\ImportUploaded;
use App\Exceptions\DuplicateImportException;
use App\Models\Import;
use App\Models\Marketplace;
use App\Services\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CreateImportAction
{
    public function __construct(
        private readonly StorageService $storageService,
        private readonly DetectDuplicateImportAction $detectDuplicateImportAction,
    ) {
    }

    /**
     * @throws DuplicateImportException
     */
    public function handle(int $userId, Marketplace $marketplace, UploadedFile $file): Import
    {
        $hash = hash_file('sha256', $file->getRealPath());

        if ($existing = $this->detectDuplicateImportAction->handle($userId, $hash)) {
            throw new DuplicateImportException($existing);
        }

        $disk = 's3';
        $path = "imports/{$userId}/".Str::uuid()."/{$file->getClientOriginalName()}";
        $this->storageService->put($disk, $path, $file->getContent());

        $import = Import::create([
            'user_id' => $userId,
            'marketplace_id' => $marketplace->id,
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'disk' => $disk,
            'file_hash' => $hash,
            'file_size' => $file->getSize(),
            'status' => ImportStatus::Pending,
            'imported_at' => now(),
        ]);

        ImportUploaded::dispatch($import);

        return $import;
    }
}
