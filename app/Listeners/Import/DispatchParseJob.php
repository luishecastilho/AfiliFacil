<?php

namespace App\Listeners\Import;

use App\Events\Import\ImportUploaded;
use App\Jobs\ParseImportJob;

class DispatchParseJob
{
    public function handle(ImportUploaded $event): void
    {
        ParseImportJob::dispatch($event->import);
    }
}
