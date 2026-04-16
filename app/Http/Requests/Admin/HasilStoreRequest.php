<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class HasilStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sourceRules = $this->isJumaatFlow()
            ? ['nullable', 'integer', 'exists:sumber_hasil,id']
            : ['required', 'integer', 'exists:sumber_hasil,id'];

        return [
            'id_masjid' => ['nullable', 'integer', 'exists:masjid,id'],
            'tarikh' => ['required', 'date'],
            'amaun' => ['required', 'numeric', 'min:0.01'],
            'id_akaun' => ['required', 'integer', 'exists:akaun,id'],
            'id_sumber_hasil' => $sourceRules,
            'id_tabung_khas' => ['nullable', 'integer', 'exists:tabung_khas,id'],
            'is_jumaat' => ['nullable', 'boolean'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'amaun' => is_numeric($this->input('amaun')) ? (float) $this->input('amaun') : $this->input('amaun'),
            'is_jumaat' => $this->boolean('is_jumaat'),
            'catatan' => trim((string) $this->input('catatan')) ?: null,
            'id_tabung_khas' => $this->filled('id_tabung_khas') ? $this->input('id_tabung_khas') : null,
        ]);
    }

    private function isJumaatFlow(): bool
    {
        return $this->routeIs('admin.hasil.jumaat.*');
    }
}
