<?php

namespace App\Http\Requests\Draw;

use App\Enum\DrawStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', new Enum(DrawStatus::class)],
            'allow_multiple_wins' => ['sometimes', 'boolean'],
            'is_public' => ['sometimes', 'boolean'],
            'registration_until' => ['nullable', 'date'],
        ];
    }
}
