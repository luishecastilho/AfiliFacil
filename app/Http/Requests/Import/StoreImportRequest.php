<?php

namespace App\Http\Requests\Import;

use App\Models\Import;
use Illuminate\Foundation\Http\FormRequest;

class StoreImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Import::class);
    }

    public function rules(): array
    {
        return [
            'marketplace_id' => ['required', 'exists:marketplaces,id'],
            'file' => [
                'required',
                'file',
                'mimes:csv,txt,xlsx,xls',
                'max:'.config('afilifacil.import.max_file_size_kb'),
            ],
        ];
    }
}
