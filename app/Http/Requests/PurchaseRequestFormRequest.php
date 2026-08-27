<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequestFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $description = trim((string) $this->input('description'));

        $this->merge([
            'description' => $description === '' ? null : $description,
        ]);
    }

    public function rules(): array
    {
        return [
            'goods_id' => 'required|integer|exists:goods,id',
            'description' => 'nullable|string|max:500',
        ];
    }
}
