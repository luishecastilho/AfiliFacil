<?php

namespace App\Http\Requests\Issuer;

use Illuminate\Foundation\Http\FormRequest;

class UploadCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'certificate' => ['required', 'file', 'mimes:pfx,p12', 'max:512'],
            'certificate_password' => ['required', 'string', 'max:255'],
        ];
    }
}
