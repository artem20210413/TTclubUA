<?php

namespace App\Services\Gemini\Prompt;

use App\Models\User;

class Prompt
{
    public static function buildBirthdayPrompt(User $user): string
    {
        $languageMap = [
            'uk' => 'українською мовою',
            'ru' => 'російською мовою',
            'en' => 'англійською мовою',
            'sk' => 'словацькою мовою',
        ];

        $langText = $languageMap[$user->language ?? 'uk'];
        $styleText = EnumPromptStyle::FRIENDLY->description();

        // Собираем все автомобили пользователя
        $carsText = '';
        if ($user->cars && $user->cars->count() > 0) {
            $carDetails = $user->cars->map(function ($car) {
                $model = $car->model->name ?? '';
                $generation = $car->gene->name ?? '';
                $color = $car->color->name ?? '';
                $desc = trim("{$model} {$generation} $color");

                return $desc;
            })->implode(', ');

            $carsText = "Користувач має Audi TT: {$carDetails}.";
        }

        // Особенности покупки TT
        $ttReason = $user->why_tt ? "Купив Audi TT тому, що {$user->why_tt}." : '';

        return trim("
        Створи {$langText} {$styleText} привітання з днем народження.
        {$ttReason}
        {$carsText}
        Ім'я: {$user->name}
        Вимоги:
        - Привітання повинно звучати природно.
        - Уникати зайвих повторів.
        - Ніяких шаблонних фраз типу «від усього серця».
        - Довжина — 2–4 речення.
    ");
    }
}
