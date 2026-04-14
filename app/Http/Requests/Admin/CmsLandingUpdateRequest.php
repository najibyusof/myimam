<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CmsLandingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_masjid_id' => ['nullable', 'integer', 'exists:masjid,id'],
            'hero_title' => ['required', 'string', 'max:200'],
            'hero_subtitle' => ['nullable', 'string', 'max:1000'],
            'hero_cta_text' => ['nullable', 'string', 'max:100'],
            'hero_image' => ['nullable', 'string', 'max:255'],
            'features_items' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'hero_title' => trim((string) $this->hero_title),
            'hero_subtitle' => trim((string) $this->hero_subtitle) ?: null,
            'hero_cta_text' => trim((string) $this->hero_cta_text) ?: null,
            'hero_image' => trim((string) $this->hero_image) ?: null,
            'features_items' => trim((string) $this->features_items) ?: null,
            'footer_text' => trim((string) $this->footer_text) ?: null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
