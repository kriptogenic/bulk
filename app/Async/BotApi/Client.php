<?php

namespace App\Async\BotApi;

interface Client
{
    public function execute(string $token, string $method, int $chat_id, array $data): Message;
}