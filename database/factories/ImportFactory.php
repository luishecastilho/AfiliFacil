<?php

namespace Database\Factories;

use App\Enums\ImportStatus;
use App\Models\Import;
use App\Models\Marketplace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Import>
 */
class ImportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'marketplace_id' => Marketplace::factory(),
            'original_filename' => fake()->word().'.csv',
            'storage_path' => 'imports/'.fake()->uuid().'.csv',
            'disk' => 's3',
            'file_hash' => hash('sha256', fake()->uuid()),
            'file_size' => fake()->numberBetween(1024, 5_000_000),
            'status' => ImportStatus::Pending,
            'reference_month' => fake()->dateTimeBetween('-12 months', 'now')->format('Y-m'),
            'imported_at' => now(),
        ];
    }

    public function validated(): static
    {
        return $this->state(fn () => [
            'status' => ImportStatus::Validated,
            'total_rows' => 100,
            'valid_rows' => 90,
            'invalid_rows' => 5,
            'duplicate_rows' => 5,
            'total_amount' => fake()->randomFloat(2, 1000, 50000),
            'total_unique_tax_ids' => 40,
            'parsed_at' => now(),
        ]);
    }
}
