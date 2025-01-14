<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FastArrayIntegerDistinctRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            return;
        }

        $hashmap = [];
        foreach ($value as $item) {
            if (is_int($item)) {
                $intChatId = $item;
            } elseif (is_string($item) && ctype_digit($item)) {
                $intChatId = (int)$item;
            } else {
                $fail('Every chats element must be integer.');
                return;
            }
            if (array_key_exists($intChatId, $hashmap)) {
                $fail('Chats must be unique.');
                return;
            }
            $hashmap[$intChatId] = true;
        }
    }
}
