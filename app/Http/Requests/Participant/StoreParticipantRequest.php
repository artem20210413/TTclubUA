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
            'weight' => ['required', 'integer', 'min:1'],
            'name_manual' => ['required', 'string'],
            'contact_manual' => ['nullable', 'string'],
        ];
    }
}
