<?php

namespace App\Marketplace\Contracts;

use App\DTOs\CommissionRowDTO;
use App\Models\Marketplace;

interface MarketplaceImporterInterface
{
    /**
     * Number of rows to read per chunk when streaming the source file.
     */
    public function chunkSize(): int;

    /**
     * Stream the file at the given path, yielding one array of raw associative
     * rows (keyed by source column header) per chunk.
     *
     * @return iterable<array<int, array<string, mixed>>>
     */
    public function readChunks(string $absoluteFilePath): iterable;

    /**
     * Map a single raw row (as yielded by readChunks) into a normalized DTO,
     * using the marketplace's configured column map.
     */
    public function mapToCommissionRow(array $rawRow, Marketplace $marketplace): CommissionRowDTO;
}
