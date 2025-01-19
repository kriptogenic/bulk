<?php

declare(strict_types=1);

namespace App\Rules;

use App\Enums\SendMethod;
use App\Services\SendMethodRules;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class ParamsValidateRule implements DataAwareRule, ValidationRule
{
    private ?SendMethod $method = null;

    public function setData(array $data): void
    {
        $method = Arr::get($data, 'method');
        if (!is_string($method)) {
            return;
        }
        $this->method = SendMethod::tryFrom($method);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->method === null) {
            return;
        }

        if (!is_array($value)) {
            return;
        }

        $rules = app()->make(SendMethodRules::class)->match($this->method);
        $validator = Validator::make([
            'params' => $value,
        ], $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $fail($error);
            }
        }
    }
}
