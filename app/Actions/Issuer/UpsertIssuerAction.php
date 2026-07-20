<?php

namespace App\Actions\Issuer;

use App\Models\Issuer;
use App\Models\User;
use App\Support\TaxDocument;

class UpsertIssuerAction
{
    /**
     * @param  array<string, mixed>  $data  validated payload from UpdateIssuerRequest
     */
    public function handle(User $user, array $data): Issuer
    {
        $document = TaxDocument::sanitize((string) $data['tax_document']);
        $data['tax_document'] = $document;
        $data['document_type'] = strlen($document) === 14 ? 'cnpj' : 'cpf';

        /** @var Issuer $issuer */
        $issuer = $user->issuer()->updateOrCreate([], $data);

        return $issuer;
    }
}
