<?php

namespace App\Observers;

use App\Enums\ImportStatus;
use App\Events\Import\ImportFailed;
use App\Events\Import\ImportParsed;
use App\Models\Import;

class ImportObserver
{
    public function updated(Import $import): void
    {
        if (! $import->wasChanged('status')) {
            return;
        }

        match ($import->status) {
            ImportStatus::Validated => ImportParsed::dispatch($import),
            ImportStatus::Failed => ImportFailed::dispatch($import, $import->error_message),
            default => null,
        };
    }
}
