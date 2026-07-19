<?php

namespace App\Actions\Invoice;

use App\Enums\InvoiceEventType;
use App\Enums\InvoiceStatus;
use App\Models\Import;
use App\Models\Invoice;
use Illuminate\Support\Collection;

class GroupRowsForInvoicingAction
{
    /**
     * Groups valid (and optionally duplicate) ImportRows by (seller_id, reference_month)
     * and creates one Invoice per group, unless one already exists for that combination.
     *
     * @return Collection<int, Invoice>
     */
    public function handle(Import $import): Collection
    {
        $rows = $import->importRows()
            ->whereIn('status', ['valid', 'duplicate'])
            ->get()
            ->groupBy(fn ($row) => "{$row->seller_id}:{$row->reference_month}");

        return $rows->map(function ($groupRows) use ($import) {
            $first = $groupRows->first();

            $invoice = Invoice::firstOrCreate(
                [
                    'import_id' => $import->id,
                    'seller_id' => $first->seller_id,
                    'reference_month' => $first->reference_month,
                ],
                [
                    'status' => InvoiceStatus::Queued,
                    'amount' => $groupRows->sum('invoice_amount'),
                ]
            );

            if ($invoice->wasRecentlyCreated) {
                $invoice->importRows()->attach($groupRows->pluck('id'));
                $groupRows->toQuery()->update(['status' => 'queued']);
                $invoice->events()->create(['event' => InvoiceEventType::Queued]);
            }

            return $invoice;
        })->values();
    }
}
