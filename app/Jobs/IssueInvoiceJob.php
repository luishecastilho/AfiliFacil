<?php

namespace App\Jobs;

use App\Actions\Invoice\IssueInvoiceAction;
use App\Enums\InvoiceEventType;
use App\Enums\InvoiceStatus;
use App\Exceptions\InvoiceProviderException;
use App\Models\Invoice;
use App\Services\SubscriptionService;
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

    public function __construct(public readonly Invoice $invoice) {}

    public function backoff(): array
    {
        return [10, 30, 60, 300, 900];
    }

    public function handle(IssueInvoiceAction $issueInvoiceAction, SubscriptionService $subscriptionService): void
    {
        $user = $this->invoice->import->user;

        if (! $subscriptionService->canIssueInvoice($user)) {
            $this->invoice->update([
                'status' => InvoiceStatus::Failed,
            ]);
            $this->invoice->events()->create([
                'event' => InvoiceEventType::Failed,
                'metadata' => ['error' => 'Limite do plano atingido'],
            ]);

            return;
        }

        $issuerId = $this->invoice->import->user->issuer?->id ?? 0;

        Redis::throttle('invoice-provider:'.$issuerId)->allow(10)->every(60)->then(
            function () use ($issueInvoiceAction, $subscriptionService, $user) {
                $invoice = $issueInvoiceAction->handle($this->invoice);

                if ($invoice->status === InvoiceStatus::Generated) {
                    $subscriptionService->incrementUsage($user);
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
        $this->invoice->update(['status' => InvoiceStatus::Failed]);
    }
}
