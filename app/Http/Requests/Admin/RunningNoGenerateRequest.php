<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RunningNoGenerateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_masjid' => ['nullable', 'integer', 'exists:masjid,id'],
            'prefix'    => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]+$/'],
            'tahun'     => ['required', 'integer', 'min:2000', 'max:2100'],
            'bulan'     => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }
}
