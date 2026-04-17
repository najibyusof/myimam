<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CmsBuilderUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'target_masjid_id' => ['nullable', 'integer', 'exists:masjid,id'],
            'title' => ['required', 'string', 'max:200'],
            'seo_title' => ['nullable', 'string', 'max:200'],
            'seo_meta_description' => ['nullable', 'string', 'max:320'],
            'content_json' => ['required', 'json'],
            'is_active' => ['nullable', 'boolean'],
            'action' => ['nullable', 'in:save,publish,unpublish'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title')),
            'seo_title' => trim((string) $this->input('seo_title')) ?: null,
            'seo_meta_description' => trim((string) $this->input('seo_meta_description')) ?: null,
            'is_active' => $this->boolean('is_active'),
            'action' => (string) $this->input('action', 'save'),
        ]);
    }
}
