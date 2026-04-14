<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MasjidStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:100', 'alpha_dash', 'unique:masjid,code'],
            'alamat' => ['nullable', 'string', 'max:500'],
            'daerah' => ['nullable', 'string', 'max:100'],
            'negeri' => ['nullable', 'string', 'max:100'],
            'no_pendaftaran' => ['nullable', 'string', 'max:100'],
            'tarikh_daftar' => ['nullable', 'date'],
            'status' => ['required', 'in:active,suspended,pending'],
            'subscription_status' => ['required', 'in:active,expired,trial,none'],
            'subscription_expiry' => ['nullable', 'date'],
            'admin_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'admin_name' => ['nullable', 'string', 'max:150', 'required_with:admin_email,admin_password'],
            'admin_email' => ['nullable', 'email', 'max:255', 'unique:users,email', 'required_without:admin_user_id'],
            'admin_password' => ['nullable', 'string', 'min:8', 'required_with:admin_email'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama' => trim($this->nama),
            'code' => trim((string) $this->code) ?: null,
            'alamat' => trim($this->alamat) ?: null,
            'daerah' => trim($this->daerah) ?: null,
            'negeri' => trim($this->negeri) ?: null,
            'no_pendaftaran' => trim($this->no_pendaftaran) ?: null,
            'admin_name' => trim((string) $this->admin_name) ?: null,
            'admin_email' => trim((string) $this->admin_email) ?: null,
        ]);
    }
}
