<?php

declare(strict_types=1);

namespace App\Http;

use Exception;

class TelegramApi
{
    private const API_ENDPOINT = 'https://api.telegram.org/bot';

    public function httpApiCall(string $token, string $method, array $params = []): array
    {
        $ch = curl_init(self::API_ENDPOINT . $token . '/' . $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!empty($params)) {
            curl_setopt_array($ch, [
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($params)
            ]);
        }

        $response = curl_exec($ch);

        if ($response === false) {
            throw new Exception(curl_error($ch));
        }

        $json_response = json_decode($response, flags: JSON_THROW_ON_ERROR | JSON_OBJECT_AS_ARRAY);
        curl_close($ch);

        return $json_response;
    }

    public function getMe(string $token): array
    {
        return $this->httpApiCall($token, 'getMe');
    }
}