<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TabungKhasStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_masjid' => ['nullable', 'integer', 'exists:masjid,id'],
            'nama_tabung' => ['required', 'string', 'max:150'],
            'aktif' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama_tabung' => trim((string) $this->input('nama_tabung')),
            'aktif' => $this->boolean('aktif'),
        ]);
    }
}
