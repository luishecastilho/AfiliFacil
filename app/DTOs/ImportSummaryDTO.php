<?php

namespace App\DTOs;

final readonly class ImportSummaryDTO
{
    public function __construct(
        public int $totalRows,
        public int $validRows,
        public int $invalidRows,
        public int $duplicateRows,
        public float $totalAmount,
        public int $totalUniqueTaxIds,
        public ?string $referenceMonth,
    ) {
    }
}
