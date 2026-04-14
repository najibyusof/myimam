<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PindahanAkaunStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_masjid'     => ['nullable', 'integer', 'exists:masjid,id'],
            'tarikh'        => ['required', 'date'],
            'dari_akaun_id' => ['required', 'integer', 'exists:akaun,id'],
            'ke_akaun_id'   => ['required', 'integer', 'exists:akaun,id', 'different:dari_akaun_id'],
            'amaun'         => ['required', 'numeric', 'min:0.01'],
            'catatan'       => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'ke_akaun_id.different' => 'Akaun tujuan mesti berbeza daripada akaun sumber.',
        ];
    }
}
