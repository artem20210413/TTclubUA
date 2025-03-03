<?php

namespace App\Http\Requests\Car;

use Illuminate\Foundation\Http\FormRequest;

class CreateCarRequest extends FormRequest
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
            'user_id' => 'nullable|exists:users,id',
            'gene_id' => 'required|exists:car_genes,id',
            'model_id' => 'required|exists:car_models,id',
            'color_id' => 'required|exists:colors,id',
            'name' => 'nullable|string|max:255',
            'vin_code' => 'nullable|string|max:64|unique:cars,vin_code',
            'license_plate' => 'required|string|max:15|unique:cars,license_plate',
            'personalized_license_plate' => 'nullable|string|max:20|unique:cars,personalized_license_plate',
        ];
    }
}
