<?php

namespace App\Events\Invoice;

use App\Models\Invoice;
use App\Models\InvoiceFile;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceDownloaded
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Invoice $invoice, public readonly InvoiceFile $file)
    {
    }
}
