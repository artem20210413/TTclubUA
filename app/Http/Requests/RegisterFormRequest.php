<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterFormRequest extends FormRequest
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
            'phone' => 'required|string|unique:users',
            'telegram_nickname' => 'nullable|string|unique:users',
            'instagram_nickname' => 'nullable|string|unique:users',
            'cities' => 'nullable|array',
            'cities.*' => 'integer|exists:cities,id',
            'birth_date' => 'nullable', //|date_format:d-m-Y если дата не null, то должна быть в формате Y-m-d
            'occupation_description' => 'nullable|string',
            'why_tt' => 'nullable|string',
            'mail_address' => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
            'profile_photo' => 'nullable|max:20480',

            'no_tt_friend' => 'nullable|boolean',

            'car' => 'exclude_if:no_tt_friend,1|nullable|array',
            'car.gene_id' => 'exclude_if:no_tt_friend,1|required|exists:car_genes,id',
            'car.model_id' => 'exclude_if:no_tt_friend,1|required|exists:car_models,id',
            'car.color_id' => 'exclude_if:no_tt_friend,1|required|exists:colors,id',
            'car.name' => 'exclude_if:no_tt_friend,1|nullable|string|max:255',
            'car.vin_code' => 'exclude_if:no_tt_friend,1|nullable|string|max:64|unique:cars,vin_code',
            'car.license_plate' => 'exclude_if:no_tt_friend,1|required|string|regex:/^[A-Za-zА-Яа-яІіЇїЄє]{2}\d{4}[A-Za-zА-Яа-яІіЇїЄє]{2}$/u|unique:cars,license_plate',
            'car.personalized_license_plate' => 'exclude_if:no_tt_friend,1|nullable|string|max:20|unique:cars,personalized_license_plate',
            'car.photo' => 'nullable|max:20480', // Макс. 20 MB |mimes:jpeg,png,jpg
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Ім’я та Прізвище',
            'phone' => 'номер телефону',
            'telegram_nickname' => 'нік у Telegram',
            'instagram_nickname' => 'нік в Instagram',
            'cities' => 'місто проживання',
            'birth_date' => 'дата народження',
            'occupation_description' => 'опис діяльності',
            'why_tt' => 'чому саме Audi TT',
            'mail_address' => 'адреса для подарунку',
            'password' => 'пароль',
            'password_confirmation' => 'підтвердження паролю',
            'profile_photo' => 'фото профілю',

            'no_tt_friend' => 'пункт “Я не маю Audi TT, але хочу бути другом клубу”',

            // авто
            'car.gene_id' => 'генерація авто',
            'car.model_id' => 'модель авто',
            'car.color_id' => 'колір авто',
            'car.name' => 'назва авто',
            'car.vin_code' => 'VIN-код авто',
            'car.license_plate' => 'державний номер авто',
            'car.personalized_license_plate' => 'індивідуальний номер авто',
            'car.photo' => 'фото авто',
        ];
    }
}
