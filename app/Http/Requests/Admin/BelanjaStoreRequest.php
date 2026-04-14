<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BelanjaStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_masjid' => ['nullable', 'integer', 'exists:masjid,id'],
            'tarikh' => ['required', 'date'],
            'amaun' => ['required', 'numeric', 'min:0.01'],
            'id_akaun' => ['required', 'integer', 'exists:akaun,id'],
            'id_kategori_belanja' => ['required', 'integer', 'exists:kategori_belanja,id'],
            'id_baucar' => ['nullable', 'integer', 'exists:baucar_bayaran,id'],
            'is_submitted' => ['nullable', 'boolean'],
            'penerima' => ['nullable', 'string', 'max:190'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'amaun' => is_numeric($this->input('amaun')) ? (float) $this->input('amaun') : $this->input('amaun'),
            'is_submitted' => $this->boolean('is_submitted'),
            'penerima' => trim((string) $this->input('penerima')) ?: null,
            'catatan' => trim((string) $this->input('catatan')) ?: null,
            'id_baucar' => $this->filled('id_baucar') ? $this->input('id_baucar') : null,
        ]);
    }
}
