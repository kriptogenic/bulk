<?php

declare(strict_types=1);

namespace App\Http\Rules;

use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule;
use Yiisoft\Validator\ValidationContext;

class Enum extends Rule
{
    public function __construct(private array $list)
    {
    }

    protected function validateValue($value, ValidationContext $context = null): Result
    {
        $result = new Result();
        if (!in_array($value, $this->list)) {
            $result->addError('Value must be one of: ' . implode(', ', $this->list));
        }
        return  $result;
    }
}
