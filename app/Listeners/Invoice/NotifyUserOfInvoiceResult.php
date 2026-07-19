<?php

namespace App\Listeners\Invoice;

use App\Events\Invoice\InvoiceFailed;
use App\Events\Invoice\InvoiceGenerated;

class NotifyUserOfInvoiceResult
{
    public function handleInvoiceGenerated(InvoiceGenerated $event): void
    {
        // Per-invoice generation is chatty; user-facing notification happens
        // once per import batch via InvoicesGeneratedNotification (GenerateZipJob).
    }

    public function handleInvoiceFailed(InvoiceFailed $event): void
    {
        // TODO: notify the user once retries are exhausted, not on every attempt.
    }
}
