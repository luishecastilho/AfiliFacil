<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Import;
use App\Models\Invoice;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'import_id' => Import::factory(),
            'seller_id' => Seller::factory(),
            'status' => InvoiceStatus::Queued,
            'reference_month' => fake()->dateTimeBetween('-12 months', 'now')->format('Y-m'),
            'amount' => fake()->randomFloat(2, 100, 20000),
        ];
    }

    public function generated(): static
    {
        return $this->state(fn () => [
            'status' => InvoiceStatus::Generated,
            'invoice_number' => (string) fake()->numberBetween(100000, 999999),
            'access_key' => fake()->numerify(str_repeat('#', 44)),
            'issued_at' => now(),
            'provider' => 'null',
            'provider_reference' => fake()->uuid(),
        ]);
    }
}
