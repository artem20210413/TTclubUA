<?php

namespace App\Http\Requests\Draw;

use App\Enum\DrawStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateDrawStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(DrawStatus::class)],
        ];
    }
}
