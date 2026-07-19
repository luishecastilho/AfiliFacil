<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Notifications\SellerInvoiceNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public string $queue = 'low';

    public function __construct(public readonly Invoice $invoice)
    {
    }

    public function backoff(): array
    {
        return [15, 60, 180];
    }

    public function handle(): void
    {
        if (! $this->invoice->seller->email) {
            return;
        }

        Notification::route('mail', $this->invoice->seller->email)
            ->notify(new SellerInvoiceNotification($this->invoice));
    }
}
