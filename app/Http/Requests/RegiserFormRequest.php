<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegiserFormRequest extends FormRequest
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
//            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|unique:users',
            'telegram_nickname' => 'nullable|string|unique:users',
            'instagram_nickname' => 'nullable|string|unique:users',
            'cities' => 'nullable|array',
            'cities.*' => 'integer|exists:cities,id',
            'birth_date' => 'nullable|date_format:d-m-Y', // если дата не null, то должна быть в формате Y-m-d
            'occupation_description' => 'nullable|string',
            'why_tt' => 'nullable|string',
            'mail_address' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',

            'cars' => 'nullable|array',
            'cars.*.gene_id' => 'required|exists:car_genes,id',
            'cars.*.model_id' => 'required|exists:car_models,id',
            'cars.*.color_id' => 'required|exists:colors,id',
            'cars.*.name' => 'nullable|string|max:255',
            'cars.*.vin_code' => 'nullable|string|max:64|unique:cars,vin_code',
            'cars.*.license_plate' => 'required|string|max:15|unique:cars,license_plate',
            'cars.*.personalized_license_plate' => 'nullable|string|max:20|unique:cars,personalized_license_plate',

            'file' => 'nullable|max:20480', // Макс. 20 MB |mimes:jpeg,png,jpg
            'cars.*.file' => 'nullable|max:20480', // Макс. 20 MB |mimes:jpeg,png,jpg
        ];
    }
}
