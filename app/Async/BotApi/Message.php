<?php

namespace App\Async\BotApi;

class Message
{
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_NOT_DELIVERED = 'not_delivered';
    public const STATUS_CHAT_NOT_FOUND = 'chat_not_found';
    public const STATUS_FAILED = 'failed';
    public const STATUS_TOO_MANY_REQUESTS = 'to_many_requests';
    public const STATUS_HAVE_NO_RIGHTS = 'have_no_rights';

    public function __construct(private string $status, private int $chatId, private int $retryAfter = 0)
    {
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
