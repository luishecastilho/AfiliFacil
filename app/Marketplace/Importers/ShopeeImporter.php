<?php

namespace App\Marketplace\Importers;

use App\DTOs\CommissionRowDTO;
use App\Marketplace\Contracts\MarketplaceImporterInterface;
use App\Marketplace\Support\ColumnMapper;
use App\Models\Marketplace;
use League\Csv\Reader;

/**
 * Stub importer for Shopee commission reports (CSV/XLSX/XLS).
 *
 * TODO: wire XLSX/XLS support via maatwebsite/excel WithChunkReading;
 * this stub only handles the CSV path so the interface contract has one
 * concrete, testable implementation to build against.
 */
class ShopeeImporter implements MarketplaceImporterInterface
{
    private const DEFAULT_CHUNK_SIZE = 10000;

    public function chunkSize(): int
    {
        return config('afilifacil.import.chunk_size', self::DEFAULT_CHUNK_SIZE);
    }

    public function readChunks(string $absoluteFilePath): iterable
    {
        $csv = Reader::createFromPath($absoluteFilePath, 'r');
        $csv->setHeaderOffset(0);

        $chunk = [];

        foreach ($csv->getRecords() as $record) {
            $chunk[] = $record;

            if (count($chunk) >= $this->chunkSize()) {
                yield $chunk;
                $chunk = [];
            }
        }

        if ($chunk !== []) {
            yield $chunk;
        }
    }

    public function mapToCommissionRow(array $rawRow, Marketplace $marketplace): CommissionRowDTO
    {
        $columnMap = new ColumnMapper($marketplace->config['column_map'] ?? []);
        $mapped = $columnMap->map($rawRow);

        return new CommissionRowDTO(
            rowNumber: (int) ($mapped['row_number'] ?? 0),
            sellerName: (string) ($mapped['seller_name'] ?? ''),
            sellerDocument: preg_replace('/\D/', '', (string) ($mapped['seller_document'] ?? '')),
            sellerEmail: $mapped['seller_email'] ?? null,
            amount: (float) ($mapped['amount'] ?? 0),
            referenceMonth: (string) ($mapped['reference_month'] ?? ''),
            rawPayload: $rawRow,
        );
    }
}
