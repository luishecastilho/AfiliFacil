<?php

namespace App\Jobs;

use App\Actions\Invoice\BuildInvoiceZipAction;
use App\Models\Import;
use App\Notifications\InvoicesGeneratedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateZipJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public string $queue = 'low';

    public function __construct(public readonly Import $import) {}

    public function uniqueId(): string
    {
        return "import-zip:{$this->import->id}";
    }

    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(BuildInvoiceZipAction $buildInvoiceZipAction): void
    {
        $zipFile = $buildInvoiceZipAction->handle($this->import);

        $this->import->user->notify(new InvoicesGeneratedNotification($this->import, $zipFile));
    }
}
