<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class RetryInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('retry', $this->route('invoice'));
    }

    public function rules(): array
    {
        return [];
    }
}
