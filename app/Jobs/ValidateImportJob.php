<?php

namespace App\Jobs;

use App\Actions\Import\ValidateImportRowAction;
use App\Enums\ImportRowStatus;
use App\Enums\ImportStatus;
use App\Enums\JobExecutionStatus;
use App\Models\Import;
use App\Models\JobExecution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ValidateImportJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public string $queue = 'high';

    public function __construct(public readonly Import $import) {}

    public function uniqueId(): string
    {
        return "import:{$this->import->id}";
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(ValidateImportRowAction $validateImportRowAction): void
    {
        $execution = JobExecution::create([
            'job_class' => self::class,
            'import_id' => $this->import->id,
            'status' => JobExecutionStatus::Running,
        ]);

        $this->import->update(['status' => ImportStatus::Validating]);

        $this->flagIntraImportDuplicates();

        $this->import->importRows()
            ->where('status', ImportRowStatus::Pending)
            ->cursor()
            ->each(fn ($row) => $validateImportRowAction->handle($row));

        $this->updateImportAggregates();

        $this->import->update(['status' => ImportStatus::Validated]);

        $execution->update(['status' => JobExecutionStatus::Completed, 'finished_at' => now()]);
    }

    /**
     * Rows sharing (seller_document, reference_month) within this import are flagged
     * duplicate, keeping the first occurrence pending (so it still goes through validation).
     */
    private function flagIntraImportDuplicates(): void
    {
        $this->import->importRows()
            ->select('seller_document', 'reference_month')
            ->groupBy('seller_document', 'reference_month')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function ($group) {
                $duplicateIds = $this->import->importRows()
                    ->where('seller_document', $group->seller_document)
                    ->where('reference_month', $group->reference_month)
                    ->orderBy('row_number')
                    ->pluck('id')
                    ->skip(1);

                $this->import->importRows()
                    ->whereIn('id', $duplicateIds)
                    ->update(['status' => ImportRowStatus::Duplicate]);
            });
    }

    private function updateImportAggregates(): void
    {
        $rows = $this->import->importRows();

        $this->import->update([
            'total_rows' => (clone $rows)->count(),
            'valid_rows' => (clone $rows)->where('status', ImportRowStatus::Valid)->count(),
            'invalid_rows' => (clone $rows)->where('status', ImportRowStatus::Invalid)->count(),
            'duplicate_rows' => (clone $rows)->where('status', ImportRowStatus::Duplicate)->count(),
            'total_amount' => (clone $rows)->where('status', ImportRowStatus::Valid)->sum('invoice_amount'),
            'total_unique_tax_ids' => (clone $rows)->distinct('seller_document')->count('seller_document'),
        ]);
    }
}
