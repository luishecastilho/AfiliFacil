<?php

namespace App\Actions\Import;

use App\Enums\ImportRowStatus;
use App\Models\ImportRow;

class ValidateImportRowAction
{
    public function handle(ImportRow $row): ImportRowStatus
    {
        $errors = [];

        if ($row->seller_name === '') {
            $errors[] = 'Seller name is required.';
        }

        if (! $this->isValidTaxDocument($row->seller_document)) {
            $errors[] = 'Invalid CNPJ/CPF.';
        }

        if ($row->invoice_amount <= 0) {
            $errors[] = 'Commission amount must be greater than zero.';
        }

        if (! preg_match('/^\d{4}-\d{2}$/', $row->reference_month)) {
            $errors[] = 'Reference month must be in YYYY-MM format.';
        }

        $status = $errors === [] ? ImportRowStatus::Valid : ImportRowStatus::Invalid;

        $row->update([
            'status' => $status,
            'validation_errors' => $errors === [] ? null : $errors,
        ]);

        return $status;
    }

    /**
     * TODO: implement modulo-11 checksum validation for CNPJ/CPF.
     */
    private function isValidTaxDocument(string $document): bool
    {
        return in_array(strlen($document), [11, 14], true);
    }
}
