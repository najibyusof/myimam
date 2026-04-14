<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class KategoriBelanjaStoreRequest extends FormRequest
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
            'nama_kategori' => ['required', 'string', 'max:150'],
            'aktif' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kod' => strtoupper(trim((string) $this->input('kod'))),
            'nama_kategori' => trim((string) $this->input('nama_kategori')),
            'aktif' => $this->boolean('aktif'),
        ]);
    }
}
