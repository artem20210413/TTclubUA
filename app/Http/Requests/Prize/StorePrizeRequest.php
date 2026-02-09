<?php

namespace App\Http\Requests\Prize;

use Illuminate\Foundation\Http\FormRequest;

class StorePrizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'sort_order' => ['sometimes', 'integer'],
        ];
    }
}
