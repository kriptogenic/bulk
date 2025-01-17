<?php

declare(strict_types=1);

namespace App\Exceptions;

use DomainException;

class RetryAfterException extends DomainException
{
    /**
     * @param positive-int $retryAfter
     */
    public function __construct(public readonly int $retryAfter)
    {
        parent::__construct('Retry after');
    }
}

