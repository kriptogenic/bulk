<?php

namespace App\Http\Exceptions;

class ValidationException extends ApiException
{
    public function __construct(array $errorData)
    {
        $this->errorData = $errorData;
        parent::__construct('Bad request', 400);
    }
}
