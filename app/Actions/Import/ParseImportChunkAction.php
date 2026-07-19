<?php

namespace App\Actions\Import;

use App\Actions\Seller\UpsertSellerAction;
use App\DTOs\CommissionRowDTO;
use App\Models\Import;
use Illuminate\Support\Facades\DB;

class ParseImportChunkAction
{
    private const INSERT_BATCH_SIZE = 500;

    public function __construct(private readonly UpsertSellerAction $upsertSellerAction)
    {
    }

    /**
     * @param  CommissionRowDTO[]  $rows
     */
    public function handle(Import $import, array $rows): void
    {
        $records = [];

        foreach ($rows as $row) {
            $seller = $this->upsertSellerAction->handle($import->user_id, $row);

            $records[] = [
                'import_id' => $import->id,
                'seller_id' => $seller->id,
                'row_number' => $row->rowNumber,
                'seller_name' => $row->sellerName,
                'seller_document' => $row->sellerDocument,
                'seller_email' => $row->sellerEmail,
                'invoice_amount' => $row->amount,
                'reference_month' => $row->referenceMonth,
                'status' => 'pending',
                'payload' => json_encode($row->rawPayload),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($records) >= self::INSERT_BATCH_SIZE) {
                DB::table('import_rows')->insertOrIgnore($records);
                $records = [];
            }
        }

        if ($records !== []) {
            DB::table('import_rows')->insertOrIgnore($records);
        }
    }
}
