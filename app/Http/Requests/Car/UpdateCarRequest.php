<?php

namespace App\Http\Requests\Car;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCarRequest extends FormRequest
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
        $carId = (int)$this->route('id');

        return [
            'user_id' => 'nullable|exists:users,id',
            'gene_id' => 'nullable|exists:car_genes,id',
            'model_id' => 'nullable|exists:car_models,id',
            'name' => 'nullable|string|max:255',
            'vin_code' => 'nullable|string|max:64|unique:cars,vin_code,' . $carId,
            'license_plate' => 'nullable|string|max:15|unique:cars,license_plate,' . $carId,
            'personalized_license_plate' => 'nullable|string|max:20|unique:cars,personalized_license_plate,' . $carId,
        ];
    }
}
