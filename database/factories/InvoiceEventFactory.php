<?php

namespace Database\Factories;

use App\Enums\InvoiceEventType;
use App\Models\Invoice;
use App\Models\InvoiceEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceEvent>
 */
class InvoiceEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'event' => fake()->randomElement(InvoiceEventType::cases()),
            'created_at' => now(),
        ];
    }
}
