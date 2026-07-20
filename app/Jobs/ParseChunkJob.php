<?php

namespace App\Jobs;

use App\Actions\Import\ParseImportChunkAction;
use App\DTOs\CommissionRowDTO;
use App\Models\Import;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ParseChunkJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public string $queue = 'high';

    /**
     * @param  CommissionRowDTO[]  $rows
     */
    public function __construct(public readonly Import $import, public readonly array $rows) {}

    public function backoff(): array
    {
        return [15, 60, 180];
    }

    public function handle(ParseImportChunkAction $action): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $action->handle($this->import, $this->rows);
    }
}
