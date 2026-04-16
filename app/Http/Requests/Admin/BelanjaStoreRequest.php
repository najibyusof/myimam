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
            'submit_action' => ['nullable', 'in:draft,submitted'],
            'penerima' => ['nullable', 'string', 'max:190'],
            'catatan' => ['nullable', 'string'],
            'bukti_fail' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'bukti_fail_camera' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'amaun' => is_numeric($this->input('amaun')) ? (float) $this->input('amaun') : $this->input('amaun'),
            'submit_action' => in_array($this->input('submit_action'), ['draft', 'submitted'], true)
                ? $this->input('submit_action')
                : 'submitted',
            'penerima' => trim((string) $this->input('penerima')) ?: null,
            'catatan' => trim((string) $this->input('catatan')) ?: null,
            'id_baucar' => $this->filled('id_baucar') ? $this->input('id_baucar') : null,
        ]);
    }
}
