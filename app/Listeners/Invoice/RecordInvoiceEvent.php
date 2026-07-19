<?php

namespace App\Listeners\Invoice;

use App\Enums\InvoiceEventType;
use App\Events\Invoice\InvoiceDownloaded;

class RecordInvoiceEvent
{
    public function handle(InvoiceDownloaded $event): void
    {
        $event->invoice->events()->create(['event' => InvoiceEventType::Downloaded]);
    }
}
