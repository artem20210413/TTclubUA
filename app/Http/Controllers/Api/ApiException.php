<?php

namespace App\Http\Controllers\Api;

use JetBrains\PhpStorm\Pure;

class ApiException extends \Exception
{
    #[Pure]
    public function __construct(
        private string $errorMessage,
        private int    $errorCode = 0,
        private int    $errorStatus = 400
    )
    {
        parent::__construct($this->errorMessage, $this->errorCode, null);
    }

    public function getStatus(): int
    {
        return $this->errorStatus;
    }

}
