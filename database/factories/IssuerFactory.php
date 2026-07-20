<?php

namespace Database\Factories;

use App\Models\Issuer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Issuer>
 */
class IssuerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tax_document' => '11222333000181',
            'document_type' => 'cnpj',
            'legal_name' => fake()->company(),
            'trade_name' => fake()->companySuffix(),
            'inscricao_municipal' => (string) fake()->numberBetween(100000, 999999),
            'address_street' => fake()->streetName(),
            'address_number' => (string) fake()->buildingNumber(),
            'address_district' => fake()->citySuffix(),
            'address_city' => 'São Paulo',
            'address_state' => 'SP',
            'address_zip' => '01000000',
            'address_ibge_code' => '3550308',
            'regime_tributario' => 'simples_nacional',
            'service_code' => '10.05',
            'iss_rate' => 2.5,
            'iss_withheld' => false,
            'ambiente' => 'producao_restrita',
            'emission_mode' => 'automated',
            'dps_serie' => '00001',
            'dps_proximo_numero' => 1,
        ];
    }
}
