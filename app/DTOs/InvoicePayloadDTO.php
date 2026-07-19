<?php

namespace App\DTOs;

/**
 * Normalized data passed to an InvoiceProviderInterface implementation to issue one NF-e.
 */
final readonly class InvoicePayloadDTO
{
    /**
     * @param  array{name: string, tax_document: string, ...}  $issuer  The authenticated user's own company
     *                                                                  data, used as the NF-e issuer.
     */
    public function __construct(
        public int $invoiceId,
        public string $referenceMonth,
        public float $amount,
        public SellerDTO $seller,
        public array $issuer,
    ) {
    }
}
