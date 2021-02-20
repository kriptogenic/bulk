<?php

namespace App\Http\Exceptions;

use Throwable;

class InvalidJsonInput extends ApiException
{
    public function __construct()
    {
        parent::__construct('Json is not valid', 400);
    }
}
