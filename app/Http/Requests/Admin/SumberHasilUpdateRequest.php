<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SumberHasilUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_masjid' => ['nullable', 'integer', 'exists:masjid,id'],
            'kod' => ['required', 'string', 'max:20'],
            'nama_sumber' => ['required', 'string', 'max:150'],
            'jenis' => ['required', 'string', 'max:50'],
            'aktif' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kod' => strtoupper(trim((string) $this->input('kod'))),
            'nama_sumber' => trim((string) $this->input('nama_sumber')),
            'jenis' => trim((string) $this->input('jenis')),
            'aktif' => $this->boolean('aktif'),
        ]);
    }
}
