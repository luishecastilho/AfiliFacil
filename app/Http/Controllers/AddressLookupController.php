<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Looks up a Brazilian address by CEP via ViaCEP (returns the IBGE municipality code),
 * so non-technical users don't have to fill address/IBGE fields by hand.
 */
class AddressLookupController extends Controller
{
    public function show(string $cep): JsonResponse
    {
        $cep = preg_replace('/\D/', '', $cep) ?? '';

        if (strlen($cep) !== 8) {
            return response()->json(['message' => 'CEP inválido.'], 422);
        }

        $data = Cache::remember("cep:{$cep}", now()->addDay(), function () use ($cep) {
            $response = Http::timeout(8)->acceptJson()->get("https://viacep.com.br/ws/{$cep}/json/");

            if (! $response->successful()) {
                return null;
            }

            $body = $response->json();

            return (is_array($body) && ! ($body['erro'] ?? false)) ? $body : null;
        });

        if ($data === null) {
            return response()->json(['message' => 'CEP não encontrado.'], 404);
        }

        return response()->json([
            'address_street' => $data['logradouro'] ?? '',
            'address_district' => $data['bairro'] ?? '',
            'address_city' => $data['localidade'] ?? '',
            'address_state' => $data['uf'] ?? '',
            'address_ibge_code' => $data['ibge'] ?? '',
        ]);
    }
}
