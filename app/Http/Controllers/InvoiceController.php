<?php

namespace App\Http\Controllers;

use App\Actions\Invoice\RetryInvoiceAction;
use App\Http\Requests\Invoice\GenerateInvoicesRequest;
use App\Http\Requests\Invoice\RetryInvoiceRequest;
use App\Jobs\GenerateInvoicesJob;
use App\Models\Import;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $invoices = Invoice::with(['import', 'seller'])
            ->whereHas('import', fn ($query) => $query->where('user_id', $request->user()->id))
            ->latest()
            ->paginate(20);

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    public function show(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice->load(['seller', 'files', 'events']),
        ]);
    }

    public function generate(GenerateInvoicesRequest $request, Import $import): RedirectResponse
    {
        GenerateInvoicesJob::dispatch($import);

        return back()->with('status', 'Invoice generation started.');
    }

    public function retry(RetryInvoiceRequest $request, Invoice $invoice, RetryInvoiceAction $retryInvoiceAction): RedirectResponse
    {
        $retryInvoiceAction->handle($invoice);

        return back()->with('status', 'Invoice retry queued.');
    }

    public function progress(Import $import): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $import);

        return response()->json(
            $import->invoices()->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')
        );
    }
}
