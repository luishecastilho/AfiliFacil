<?php

namespace App\Jobs;

use App\Actions\Invoice\IssueInvoiceAction;
use App\Exceptions\InvoiceProviderException;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class IssueInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $maxExceptions = 5;

    public string $queue = 'default';

    public function __construct(public readonly Invoice $invoice)
    {
    }

    public function backoff(): array
    {
        return [10, 30, 60, 300, 900];
    }

    public function handle(IssueInvoiceAction $issueInvoiceAction): void
    {
        Redis::throttle('invoice-provider')->allow(10)->every(60)->then(
            function () use ($issueInvoiceAction) {
                $invoice = $issueInvoiceAction->handle($this->invoice);

                if ($invoice->status === \App\Enums\InvoiceStatus::Generated) {
                    UploadInvoiceFilesJob::dispatch($invoice);
                }
            },
            function () {
                $this->release(10);
            }
        );
    }

    public function failed(InvoiceProviderException $exception): void
    {
        $this->invoice->update(['status' => \App\Enums\InvoiceStatus::Failed]);
    }
}
