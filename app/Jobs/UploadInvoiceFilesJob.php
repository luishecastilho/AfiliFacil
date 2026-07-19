<?php

namespace App\Jobs;

use App\Enums\InvoiceFileType;
use App\Models\Invoice;
use App\Services\StorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class UploadInvoiceFilesJob implements ShouldQueue
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

    public function handle(StorageService $storageService): void
    {
        $payload = $this->invoice->provider_payload ?? [];

        foreach (['pdf' => InvoiceFileType::Pdf, 'xml' => InvoiceFileType::Xml] as $key => $type) {
            $url = $payload["{$key}_url"] ?? null;

            if (! $url) {
                continue;
            }

            $contents = Http::get($url)->body();
            $path = "invoices/{$this->invoice->import->user_id}/{$this->invoice->id}/invoice.{$key}";

            $storageService->put('s3', $path, $contents);

            $this->invoice->files()->create([
                'type' => $type,
                'disk' => 's3',
                'storage_path' => $path,
                'size' => strlen($contents),
            ]);
        }
    }
}
