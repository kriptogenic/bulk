<?php

declare(strict_types=1);

function array_only_keys(array $array, array $keys): array
{
    $result = [];

    foreach ($keys as $key) {
        if (array_key_exists($key, $array)) {
            $result[$key] = $array[$key];
        }
    }

    return $result;
}