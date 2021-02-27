<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Exceptions\TelegramMethodCallException;

class TelegramApiCallTester
{
    public function __construct(private TelegramApi $api, private string $test_token, private int $test_chat_id)
    {
    }

    /**
     * @throws TelegramMethodCallException
     */
    public function sendMessage(array $params): void
    {
        $params['chat_id'] = $this->test_chat_id;
        $result = $this->api->httpApiCall($this->test_token, 'sendMessage', $params);

        if ($result['ok'])
            return;

        throw new TelegramMethodCallException('sendMessage', $result);
    }
}
