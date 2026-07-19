<?php

namespace App\Listeners\Import;

use App\Events\Import\ImportFailed;
use App\Events\Import\ImportParsed;
use App\Notifications\ImportCompletedNotification;
use App\Notifications\ImportFailedNotification;

class NotifyUserOfImportResult
{
    public function handleImportParsed(ImportParsed $event): void
    {
        $event->import->user->notify(new ImportCompletedNotification($event->import));
    }

    public function handleImportFailed(ImportFailed $event): void
    {
        $event->import->user->notify(new ImportFailedNotification($event->import));
    }
}
