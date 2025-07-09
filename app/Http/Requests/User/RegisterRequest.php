<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|unique:users',
            'telegram_nickname' => 'nullable|string|unique:users',
            'instagram_nickname' => 'nullable|string|unique:users',
            'cities' => 'nullable|array',
            'cities.*' => 'integer|exists:cities,id',
            'birth_date' => 'nullable|date_format:d-m-Y', // если дата не null, то должна быть в формате Y-m-d
//            'club_entry_date' => 'nullable|date_format:d-m-Y', // если дата обязательна, то тоже проверка на формат
            'occupation_description' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
