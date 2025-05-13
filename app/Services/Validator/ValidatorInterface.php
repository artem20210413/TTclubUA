<?php

namespace App\Services\Validator;

interface ValidatorInterface
{
    public function validate(array $data): array; // Возвращает массив ошибок
}
