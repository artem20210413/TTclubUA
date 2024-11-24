<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('id') ?? request()->user()->id;

        return [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $userId,
            'phone' => 'nullable|string|unique:users,phone,' . $userId,
            'cities' => 'nullable|array',
            'cities.*' => 'integer|exists:cities,id',
            'telegram_nickname' => 'nullable|string|unique:users,telegram_nickname,' . $userId,
            'instagram_nickname' => 'nullable|string|unique:users,instagram_nickname,' . $userId,
            'birth_date' => 'nullable|date_format:d-m-Y',
            'club_entry_date' => 'nullable|date_format:d-m-Y',
            'occupation_description' => 'nullable|string',
        ];
    }
}
