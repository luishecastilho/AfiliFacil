<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class GenerateInvoicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('generateInvoices', $this->route('import'));
    }

    public function rules(): array
    {
        return [
            'import_row_ids' => ['sometimes', 'array'],
            'import_row_ids.*' => ['integer', 'exists:import_rows,id'],
        ];
    }
}
