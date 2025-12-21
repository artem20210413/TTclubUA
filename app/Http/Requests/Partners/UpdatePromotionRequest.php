<?php

namespace App\Http\Requests\Partners;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'promo_title' => 'sometimes|required|string|max:255',
            'promo_description' => 'nullable|string',
            'discount_value' => 'nullable|string|max:255',
            'promo_code' => 'nullable|string|max:255',
            'is_exclusive' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'priority' => 'sometimes|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }
}
