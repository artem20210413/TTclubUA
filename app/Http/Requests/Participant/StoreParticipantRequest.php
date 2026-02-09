<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;

class StoreParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name_manual' => ['required', 'string', 'max:255'],
            'contact_manual' => ['nullable', 'string', 'max:255'],
            'weight' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
