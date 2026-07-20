<?php

namespace App\Http\Requests\Issuer;

use App\Enums\AmbienteEmissao;
use App\Enums\EmissionMode;
use App\Enums\RegimeTributario;
use App\Support\TaxDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIssuerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'tax_document' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! TaxDocument::isValid((string) $value)) {
                        $fail('O CNPJ/CPF informado é inválido.');
                    }
                },
            ],
            'legal_name' => ['required', 'string', 'max:255'],
            'trade_name' => ['nullable', 'string', 'max:255'],
            'inscricao_municipal' => ['nullable', 'string', 'max:30'],

            'address_street' => ['nullable', 'string', 'max:255'],
            'address_number' => ['nullable', 'string', 'max:20'],
            'address_complement' => ['nullable', 'string', 'max:100'],
            'address_district' => ['nullable', 'string', 'max:100'],
            'address_city' => ['nullable', 'string', 'max:100'],
            'address_state' => ['nullable', 'string', 'size:2'],
            'address_zip' => ['nullable', 'string', 'max:10'],
            'address_ibge_code' => ['nullable', 'string', 'max:10'],

            'regime_tributario' => ['required', Rule::enum(RegimeTributario::class)],
            'service_code' => ['nullable', 'string', 'max:20'],
            'municipal_service_code' => ['nullable', 'string', 'max:20'],
            'cnae' => ['nullable', 'string', 'max:10'],
            'iss_rate' => ['nullable', 'numeric', 'between:0,100'],
            'iss_withheld' => ['boolean'],
            'ambiente' => ['required', Rule::enum(AmbienteEmissao::class)],
            'emission_mode' => ['required', Rule::enum(EmissionMode::class)],
            'dps_serie' => ['nullable', 'string', 'max:5'],
        ];
    }
}
