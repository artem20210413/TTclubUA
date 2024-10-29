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

