<?php

namespace App\Marketplace\Support;

/**
 * Maps a raw source-file header (as configured in marketplace.config['column_map'])
 * to the normalized field name expected when building a CommissionRowDTO.
 */
class ColumnMapper
{
    /**
     * @param  array<string, string>  $columnMap  Source header => normalized field name.
     */
    public function __construct(private readonly array $columnMap)
    {
    }

    /**
     * @param  array<string, mixed>  $rawRow  Keyed by source header.
     * @return array<string, mixed> Keyed by normalized field name.
     */
    public function map(array $rawRow): array
    {
        $mapped = [];

        foreach ($this->columnMap as $sourceHeader => $normalizedField) {
            $mapped[$normalizedField] = $rawRow[$sourceHeader] ?? null;
        }

        return $mapped;
    }
}
