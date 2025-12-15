<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuggestionsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'description' => 'required|string|max:4000',
            'files' => 'nullable|array',
            'files.*' => 'max:20048',
            'environment' => 'nullable|string|max:255',
        ];
    }
}
