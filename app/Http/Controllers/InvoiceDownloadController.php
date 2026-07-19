<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceFileType;
use App\Events\Invoice\InvoiceDownloaded;
use App\Jobs\GenerateZipJob;
use App\Models\Import;
use App\Models\Invoice;
use App\Models\InvoiceFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class InvoiceDownloadController extends Controller
{
    public function show(Invoice $invoice, string $type): RedirectResponse
    {
        $this->authorize('download', $invoice);

        $fileType = InvoiceFileType::from($type);

        /** @var InvoiceFile $file */
        $file = $invoice->files()->where('type', $fileType)->firstOrFail();

        InvoiceDownloaded::dispatch($invoice, $file);

        $ttl = now()->addMinutes(config('nf-facilitator.download.presigned_url_ttl_minutes'));
        $url = Storage::disk($file->disk)->temporaryUrl($file->storage_path, $ttl);

        return redirect()->away($url);
    }

    public function zip(Import $import): RedirectResponse
    {
        $this->authorize('view', $import);

        $existing = InvoiceFile::whereHas('invoice', fn ($query) => $query->where('import_id', $import->id))
            ->where('type', InvoiceFileType::Zip)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($existing) {
            $url = Storage::disk($existing->disk)->temporaryUrl(
                $existing->storage_path,
                now()->addMinutes(config('nf-facilitator.download.presigned_url_ttl_minutes')),
            );

            return redirect()->away($url);
        }

        GenerateZipJob::dispatch($import);

        return back()->with('status', 'ZIP generation started. You will be notified when it is ready.');
    }
}
