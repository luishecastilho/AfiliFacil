<?php

namespace Database\Factories;

use App\Enums\InvoiceFileType;
use App\Models\Invoice;
use App\Models\InvoiceFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceFile>
 */
class InvoiceFileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'type' => fake()->randomElement(InvoiceFileType::cases()),
            'disk' => 's3',
            'storage_path' => 'invoices/'.fake()->uuid().'.pdf',
            'size' => fake()->numberBetween(1024, 500_000),
        ];
    }
}
