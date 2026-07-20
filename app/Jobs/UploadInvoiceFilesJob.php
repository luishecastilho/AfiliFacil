<?php

namespace App\Jobs;

use App\Enums\InvoiceFileType;
use App\InvoiceProvider\Contracts\InvoiceProviderInterface;
use App\Models\Invoice;
use App\Services\StorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UploadInvoiceFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public string $queue = 'low';

    public function __construct(public readonly Invoice $invoice) {}

    public function backoff(): array
    {
        return [15, 60, 180];
    }

    public function handle(StorageService $storageService, InvoiceProviderInterface $provider): void
    {
        $payload = $this->invoice->provider_payload ?? [];
        $userId = $this->invoice->import->user_id;
        $basePath = "invoices/{$userId}/{$this->invoice->id}";

        // Authorized NFS-e XML returned by the provider.
        if (! empty($payload['nfse_xml'])) {
            $this->store(InvoiceFileType::Xml, "{$basePath}/nfse.xml", $payload['nfse_xml'], $storageService);
        }

        // DANFSE (PDF) fetched from the provider (mTLS) by access key.
        if ($this->invoice->access_key && $this->invoice->issuer_id) {
            $pdf = $provider->baixarDanfse($this->invoice->access_key, $this->invoice->issuer_id);

            if ($pdf !== null && $pdf !== '') {
                $this->store(InvoiceFileType::Pdf, "{$basePath}/danfse.pdf", $pdf, $storageService);
            }
        }
    }

    private function store(InvoiceFileType $type, string $path, string $contents, StorageService $storage): void
    {
        $storage->put('s3', $path, $contents);

        $this->invoice->files()->updateOrCreate(
            ['type' => $type],
            ['disk' => 's3', 'storage_path' => $path, 'size' => strlen($contents)],
        );
    }
}
