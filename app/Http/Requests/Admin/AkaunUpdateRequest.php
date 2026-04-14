<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AkaunUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_masjid' => ['nullable', 'integer', 'exists:masjid,id'],
            'nama_akaun' => ['required', 'string', 'max:150'],
            'jenis' => ['required', Rule::in(['tunai', 'bank'])],
            'no_akaun' => ['nullable', 'string', 'max:100', 'required_if:jenis,bank'],
            'nama_bank' => ['nullable', 'string', 'max:150', 'required_if:jenis,bank'],
            'status_aktif' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status_aktif' => $this->boolean('status_aktif'),
            'jenis' => strtolower((string) $this->input('jenis')),
        ]);
    }
}
