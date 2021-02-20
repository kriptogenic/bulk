<?php

namespace App\Http\Exceptions;

class TelegramMethodCallException extends ApiException
{
    public function __construct(string $method, array $data)
    {
        $this->errorData = ['method' => $method, 'data' => $data];
        parent::__construct('Telegram API error on sending', 400);
    }
}