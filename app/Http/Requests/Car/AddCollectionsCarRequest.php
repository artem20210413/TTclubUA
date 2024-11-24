<?php

namespace App\Http\Requests\Car;

use Illuminate\Foundation\Http\FormRequest;

class AddCollectionsCarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'images' => 'required|array', // Макс. 20 MB |mimes:jpeg,png,jpg
            'images.*' => 'required|max:20480', // Макс. 20 MB |mimes:jpeg,png,jpg
        ];
    }
}
