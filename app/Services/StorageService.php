<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class StorageService
{
    public function put(string $disk, string $path, mixed $contents): string
    {
        Storage::disk($disk)->put($path, $contents);

        return $path;
    }

    public function temporaryUrl(string $disk, string $path, \DateTimeInterface $expiresAt): string
    {
        return Storage::disk($disk)->temporaryUrl($path, $expiresAt);
    }

    public function delete(string $disk, string $path): bool
    {
        return Storage::disk($disk)->delete($path);
    }
}
