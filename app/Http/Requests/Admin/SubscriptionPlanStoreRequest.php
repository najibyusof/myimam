<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionPlanStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:100', 'unique:subscription_plans,slug'],
            'price' => ['required', 'numeric', 'min:0'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:36'],
            'features_json' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $decodedFeatures = null;

        if ($this->filled('features_json')) {
            $decodedFeatures = json_decode((string) $this->input('features_json'), true);
        }

        $this->merge([
            'name' => trim((string) $this->name),
            'slug' => trim((string) $this->slug),
            'is_active' => $this->boolean('is_active'),
            'sort_order' => $this->input('sort_order', 0),
            'features' => is_array($decodedFeatures) ? $decodedFeatures : null,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('features_json')) {
                json_decode((string) $this->input('features_json'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $validator->errors()->add('features_json', 'Features mesti dalam format JSON yang sah.');
                }
            }
        });
    }
}
