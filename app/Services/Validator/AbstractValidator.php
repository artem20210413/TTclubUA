<?php

namespace App\Services\Validator;

abstract class AbstractValidator implements ValidatorInterface
{
    protected ?ValidatorInterface $next = null;
    protected array $errors = [];

    public function setNext(ValidatorInterface $next): ValidatorInterface
    {
        $this->next = $next;
        return $next;
    }

    public function validate(array $data): array
    {
        $errors = $this->check($data);

        if ($this->next) {
            $errors = array_merge($errors, $this->next->validate($data));
        }

        return $errors;
    }

    abstract protected function check(array $data): array;
}

