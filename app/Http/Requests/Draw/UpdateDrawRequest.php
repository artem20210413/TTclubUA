<?php

namespace App\Http\Requests\Draw;

use App\Enum\DrawStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateDrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', new Enum(DrawStatus::class)],
            'allow_multiple_wins' => ['sometimes', 'boolean'],
            'is_public' => ['sometimes', 'boolean'],
            'registration_until' => ['nullable', 'date'],
        ];
    }
}
