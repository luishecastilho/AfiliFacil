<?php

namespace App\Observers;

use App\Enums\InvoiceStatus;
use App\Events\Invoice\InvoiceFailed;
use App\Events\Invoice\InvoiceGenerated;
use App\Models\Invoice;

class InvoiceObserver
{
    public function updated(Invoice $invoice): void
    {
        if (! $invoice->wasChanged('status')) {
            return;
        }

        match ($invoice->status) {
            InvoiceStatus::Generated => InvoiceGenerated::dispatch($invoice),
            InvoiceStatus::Failed => InvoiceFailed::dispatch($invoice),
            default => null,
        };
    }
}
