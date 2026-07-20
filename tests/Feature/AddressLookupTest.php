<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AddressLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_cep_lookup_returns_address_with_ibge(): void
    {
        Http::fake([
            'viacep.com.br/*' => Http::response([
                'logradouro' => 'Praça da Sé',
                'bairro' => 'Sé',
                'localidade' => 'São Paulo',
                'uf' => 'SP',
                'ibge' => '3550308',
            ], 200),
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson(route('cep.lookup', '01001-000'))
            ->assertOk()
            ->assertJson([
                'address_street' => 'Praça da Sé',
                'address_district' => 'Sé',
                'address_city' => 'São Paulo',
                'address_state' => 'SP',
                'address_ibge_code' => '3550308',
            ]);
    }

    public function test_invalid_cep_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('cep.lookup', '123'))
            ->assertStatus(422);
    }

    public function test_unknown_cep_returns_404(): void
    {
        Http::fake([
            'viacep.com.br/*' => Http::response(['erro' => true], 200),
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson(route('cep.lookup', '99999999'))
            ->assertNotFound();
    }
}
