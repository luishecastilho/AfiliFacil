<?php

namespace App\Http\Controllers;

use App\Actions\Invoice\RetryInvoiceAction;
use App\Http\Requests\Invoice\GenerateInvoicesRequest;
use App\Http\Requests\Invoice\RetryInvoiceRequest;
use App\Jobs\GenerateInvoicesJob;
use App\Models\Import;
use App\Models\Invoice;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
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

    public function generate(GenerateInvoicesRequest $request, Import $import, SubscriptionService $subscriptions): RedirectResponse
    {
        GenerateInvoicesJob::dispatch($import);

        $user = $request->user();
        $limit = $subscriptions->nfLimit($user);

        // Free/limited plans: warn right after submission when this import will
        // blow past the remaining monthly quota, nudging toward an upgrade.
        if ($limit !== null) {
            $remaining = max(0, $limit - $subscriptions->nfUsedThisMonth($user));

            if (($import->total_unique_tax_ids ?? 0) > $remaining) {
                return back()->with(
                    'warning',
                    "Esta importação gera {$import->total_unique_tax_ids} notas, mas seu plano permite apenas mais {$remaining} este mês. ".
                    'As excedentes não serão emitidas — faça upgrade em Assinatura para emitir todas.'
                );
            }
        }

        return back()->with('status', 'Geração de notas iniciada.');
    }

    public function retry(RetryInvoiceRequest $request, Invoice $invoice, RetryInvoiceAction $retryInvoiceAction): RedirectResponse
    {
        $retryInvoiceAction->handle($invoice);

        return back()->with('status', 'Invoice retry queued.');
    }

    public function progress(Import $import): JsonResponse
    {
        $this->authorize('view', $import);

        return response()->json(
            $import->invoices()->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')
        );
    }
}
