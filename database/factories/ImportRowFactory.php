<?php

namespace Database\Factories;

use App\Enums\ImportRowStatus;
use App\Models\Import;
use App\Models\ImportRow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportRow>
 */
class ImportRowFactory extends Factory
{
    public function definition(): array
    {
        return [
            'import_id' => Import::factory(),
            'row_number' => fake()->unique()->numberBetween(1, 100000),
            'seller_name' => fake()->company(),
            'seller_document' => fake()->numerify('##############'),
            'seller_email' => fake()->companyEmail(),
            'invoice_amount' => fake()->randomFloat(2, 10, 10000),
            'reference_month' => fake()->dateTimeBetween('-12 months', 'now')->format('Y-m'),
            'status' => ImportRowStatus::Pending,
            'payload' => [],
        ];
    }
}
