<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TenantSubscriptionAssignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:active,expired,cancelled,grace'],
            'grace_days' => ['nullable', 'integer', 'min:0', 'max:90'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'payment_reference' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'grace_days' => $this->input('grace_days', 7),
            'amount_paid' => $this->input('amount_paid', 0),
            'payment_reference' => trim((string) $this->payment_reference) ?: null,
            'notes' => trim((string) $this->notes) ?: null,
        ]);
    }
}
