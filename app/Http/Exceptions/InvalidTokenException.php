<?php

namespace App\Http\Exceptions;

class InvalidTokenException extends ApiException
{
    public function __construct(array $telegram_error)
    {
        $this->errorData = $telegram_error;
        parent::__construct('Token is invalid', 403);
    }
}
