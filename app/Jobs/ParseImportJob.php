<?php

namespace App\Jobs;

use App\Enums\ImportStatus;
use App\Enums\JobExecutionStatus;
use App\Marketplace\Contracts\MarketplaceImporterInterface;
use App\Models\Import;
use App\Models\JobExecution;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

class ParseImportJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 3;

    public string $queue = 'high';

    public function __construct(public readonly Import $import)
    {
    }

    public function uniqueId(): string
    {
        return "import:{$this->import->id}";
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(): void
    {
        $execution = JobExecution::create([
            'job_class' => self::class,
            'import_id' => $this->import->id,
            'status' => JobExecutionStatus::Running,
        ]);

        $this->import->update(['status' => ImportStatus::Parsing]);

        $importer = app($this->import->marketplace->importer_class ?? MarketplaceImporterInterface::class);

        $localPath = Storage::disk($this->import->disk)->path($this->import->storage_path);

        $chunkJobs = [];
        foreach ($importer->readChunks($localPath) as $chunk) {
            $rows = array_map(
                fn (array $rawRow) => $importer->mapToCommissionRow($rawRow, $this->import->marketplace),
                $chunk
            );
            $chunkJobs[] = new ParseChunkJob($this->import, $rows);
        }

        Bus::batch($chunkJobs)
            ->then(function (Batch $batch) use ($execution) {
                $this->import->update(['status' => ImportStatus::Parsed, 'parsed_at' => now()]);
                $execution->update(['status' => JobExecutionStatus::Completed, 'finished_at' => now()]);
                ValidateImportJob::dispatch($this->import);
            })
            ->catch(function (Batch $batch, \Throwable $e) use ($execution) {
                $this->import->update(['status' => ImportStatus::Failed, 'error_message' => $e->getMessage()]);
                $execution->update([
                    'status' => JobExecutionStatus::Failed,
                    'finished_at' => now(),
                    'error_message' => $e->getMessage(),
                ]);
            })
            ->name("parse-import-{$this->import->id}")
            ->onQueue('high')
            ->dispatch();
    }
}
