<?php

namespace App\Http\Exceptions;

use Exception;
use JsonSerializable;

class ApiException extends Exception
{
    protected array|JsonSerializable $errorData;

    public function hasErrorData(): bool
    {
        return isset($this->errorData);
    }

    public function getErrorData(): array|JsonSerializable
    {
        return $this->errorData;
    }
}
