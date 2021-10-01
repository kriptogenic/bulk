<?php

declare(strict_types=1);

namespace App\Http\Rules;

use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule;
use Yiisoft\Validator\ValidationContext;

final class Enum extends Rule
{
    private array $list;

    public static function rule(array $list): self
    {
        $rule = new self();
        $rule->list = $list;
        return $rule;
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
