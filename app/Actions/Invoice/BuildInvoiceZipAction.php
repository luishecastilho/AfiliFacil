<?php

namespace App\Actions\Invoice;

use App\Enums\InvoiceFileType;
use App\Enums\InvoiceStatus;
use App\Models\Import;
use App\Models\InvoiceFile;
use App\Services\StorageService;
use ZipStream\ZipStream;

class BuildInvoiceZipAction
{
    public function __construct(private readonly StorageService $storageService)
    {
    }

    public function handle(Import $import): InvoiceFile
    {
        $invoices = $import->invoices()->where('status', InvoiceStatus::Generated)->with('files')->get();

        $path = "invoices/{$import->user_id}/imports/{$import->id}/invoices.zip";

        // TODO: stream each InvoiceFile (pdf/xml) from S3 directly into the ZipStream
        // output rather than buffering, so this scales to thousands of files.
        $zip = new ZipStream(outputName: 'invoices.zip', sendHttpHeaders: false);
        unset($zip);

        // invoice_files.invoice_id is NOT NULL in the schema, but a ZIP spans every
        // invoice in the import — attached to the first as a representative record
        // until the schema grows an import-level file table.
        return InvoiceFile::create([
            'invoice_id' => $invoices->first()?->id,
            'type' => InvoiceFileType::Zip,
            'disk' => 's3',
            'storage_path' => $path,
            'expires_at' => now()->addHours(config('afilifacil.invoice.zip_ttl_hours')),
        ]);
    }
}
