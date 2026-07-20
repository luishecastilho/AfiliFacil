<?php

namespace App\Jobs;

use App\Actions\Invoice\GroupRowsForInvoicingAction;
use App\Models\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateInvoicesJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public string $queue = 'default';

    public function __construct(public readonly Import $import) {}

    public function uniqueId(): string
    {
        return "import:{$this->import->id}";
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(GroupRowsForInvoicingAction $groupRowsForInvoicingAction): void
    {
        $invoices = $groupRowsForInvoicingAction->handle($this->import);

        $invoices->each(fn ($invoice) => IssueInvoiceJob::dispatch($invoice));
    }
}
