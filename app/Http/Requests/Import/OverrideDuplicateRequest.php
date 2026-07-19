<?php

namespace App\Http\Requests\Import;

use Illuminate\Foundation\Http\FormRequest;

class OverrideDuplicateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Import::class);
    }

    public function rules(): array
    {
        return [
            'confirm' => ['required', 'accepted'],
        ];
    }
}
