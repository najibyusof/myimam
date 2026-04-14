<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MasjidUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'daerah' => ['nullable', 'string', 'max:100'],
            'negeri' => ['nullable', 'string', 'max:100'],
            'no_pendaftaran' => ['nullable', 'string', 'max:100'],
            'tarikh_daftar' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama' => trim($this->nama),
            'alamat' => trim($this->alamat) ?: null,
            'daerah' => trim($this->daerah) ?: null,
            'negeri' => trim($this->negeri) ?: null,
            'no_pendaftaran' => trim($this->no_pendaftaran) ?: null,
        ]);
    }
}
