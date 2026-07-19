<?php

namespace App\Actions\Invoice;

use App\Enums\InvoiceEventType;
use App\Enums\InvoiceStatus;
use App\Jobs\IssueInvoiceJob;
use App\Models\Invoice;

class RetryInvoiceAction
{
    public function handle(Invoice $invoice): Invoice
    {
        $invoice->update(['status' => InvoiceStatus::Retrying]);
        $invoice->events()->create(['event' => InvoiceEventType::Retried]);

        IssueInvoiceJob::dispatch($invoice);

        return $invoice;
    }
}
