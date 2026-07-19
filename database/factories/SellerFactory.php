<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seller>
 */
class SellerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tax_document' => fake()->numerify('##############'),
            'document_type' => 'cnpj',
            'name' => fake()->company(),
            'trade_name' => fake()->companySuffix(),
            'email' => fake()->companyEmail(),
            'address_street' => fake()->streetName(),
            'address_number' => (string) fake()->buildingNumber(),
            'address_district' => fake()->citySuffix(),
            'address_city' => fake()->city(),
            'address_state' => fake()->randomElement(['SP', 'RJ', 'MG', 'RS', 'PR', 'SC', 'BA']),
            'address_zip' => fake()->numerify('########'),
        ];
    }
}
