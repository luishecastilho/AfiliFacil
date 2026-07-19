<?php

namespace App\DTOs;

/**
 * Normalized commission row produced by a MarketplaceImporterInterface implementation,
 * regardless of the source marketplace's raw column layout.
 */
final readonly class CommissionRowDTO
{
    public function __construct(
        public int $rowNumber,
        public string $sellerName,
        public string $sellerDocument,
        public ?string $sellerEmail,
        public float $amount,
        public string $referenceMonth,
        public array $rawPayload,
    ) {
    }
}
