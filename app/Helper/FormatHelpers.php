<?php

use App\Http\Controllers\Api\ApiException;
use Illuminate\Database\Eloquent\Collection;

if (!function_exists('formatPhoneNumber')) {
    function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone);

        // Если номер начинается с "0", заменяем его на "380"
        if (strpos($phone, '0') === 0) {
            $phone = '380' . substr($phone, 1);
        }

        // Возвращаем номер, ограниченный до 12 символов
        return substr($phone, 0, 12);
    }

}
if (!function_exists('formatNormalizePlateNumber')) {
    function formatNormalizePlateNumber(string $plate): string {
        // Словарь замены кириллических букв на латинские
        $replaceMap = [
            'А' => 'A', 'В' => 'B', 'С' => 'C', 'Е' => 'E', 'Н' => 'H',
            'І' => 'I', 'К' => 'K', 'М' => 'M', 'О' => 'O', 'Р' => 'P',
            'Т' => 'T', 'У' => 'Y', 'Х' => 'X'
        ];

        // Приводим к верхнему регистру
        $plate = mb_strtoupper($plate, 'UTF-8');

        // Удаляем все спецсимволы, оставляя только буквы и цифры
        $plate = preg_replace('/[^A-ZА-ЯІЇЄ0-9]/u', '', $plate);

        // Если в номере есть кириллические буквы — заменяем их
        return strtr($plate, $replaceMap);
    }

}

