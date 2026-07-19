<?php

namespace App\Actions\Seller;

use App\DTOs\CommissionRowDTO;
use App\Models\Seller;

class UpsertSellerAction
{
    public function handle(int $userId, CommissionRowDTO $row): Seller
    {
        return Seller::updateOrCreate(
            [
                'user_id' => $userId,
                'tax_document' => $row->sellerDocument,
            ],
            [
                'document_type' => strlen($row->sellerDocument) === 14 ? 'cnpj' : 'cpf',
                'name' => $row->sellerName,
                'email' => $row->sellerEmail,
            ],
        );
    }
}
