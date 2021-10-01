<?php

declare(strict_types=1);

namespace App\Http\Rules;

use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule;
use Yiisoft\Validator\ValidationContext;

class ArrayRule extends Rule
{
    private bool $unique = false;

    private ?int $min = null;
    private ?int $max = null;

    public static function rule(): self
    {
        return new self;
    }

    public function validateValue($value, ValidationContext $context = null): Result
    {
        $result = new Result();

        if (!is_array($value)) {
            $result->addError('Value should be array');
            return $result;
        }

        if ($this->unique && $value !== array_unique($value)) {
            $result->addError('Array values should be unique');
        }

        if ($this->min !== null || $this->max !== null) {
            $count = count($value);
            if ($this->min !== null) {
                if ($count < $this->min) {
                    $result->addError('Array size should be more than ' . $this->min);
                }
            }

            if ($this->max !== null) {
                if ($count > $this->max) {
                    $result->addError('Array size should be less than ' . $this->max);
                }
            }
        }

        return $result;
    }

    public function unique(): self
    {
        $new = clone $this;
        $new->unique = true;
        return $new;
    }

    public function min(int $size): self
    {
        $new = clone $this;
        $new->min = $size;
        return $new;
    }

    public function max(int $size): self
    {
        $new = clone $this;
        $new->max = $size;
        return $new;
    }
}
