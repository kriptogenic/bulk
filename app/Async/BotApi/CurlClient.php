<?php

declare(strict_types=1);

namespace App\Async\BotApi;

use Exception;
use Psr\Log\LoggerInterface;
use Swoole\Coroutine\Curl\Handle;
use Throwable;

class CurlClient implements Client
{
    private string $apiEndpoint = 'https://api.telegram.org/bot';

    private Handle $curl;

    public function __construct(private LoggerInterface $logger)
    {
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->curl = curl_init();
    }

    public function execute(string $token, string $method, int $chat_id, array $data): Message
    {
        curl_setopt_array($this->curl, [
                CURLOPT_URL => $this->apiEndpoint . $token . '/' . $method,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HEADER => false,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Keep-Alive: timeout=5, max=1000',
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => json_encode($data + ['chat_id' => $chat_id])
            ]
        );

        $response = curl_exec($this->curl);
        if ($response === FALSE) {
            $this->logger->error(new Exception(
                curl_error($this->curl) . json_encode([(int)$token, $method, $data])
            ));
            return new Message(Message::STATUS_FAILED, $chat_id);
        }

        try {
            $json = json_decode($response, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            $this->logger->error($e);
            return new Message(Message::STATUS_FAILED, $chat_id);
        }

        if ($json->ok) {
            return new Message(Message::STATUS_DELIVERED, $chat_id);
        }

        if ($json->error_code === 403) {
            return new Message(Message::STATUS_NOT_DELIVERED, $chat_id);
        }

        if ($json->error_code === 400) {
            if (str_contains($json->description, 'chat not found')) {
                return new Message(Message::STATUS_CHAT_NOT_FOUND, $chat_id);
            }
            if (str_contains($json->description, 'have no rights')) {
                return new Message(Message::STATUS_HAVE_NO_RIGHTS, $chat_id);
            }
        }

        if ($json->error_code === 429) {
            $this->logger->warning('Retry after' . $json->parameters->retry_after);
            $this->logger->warning($chat_id);
            return new Message(Message::STATUS_TOO_MANY_REQUESTS, $chat_id, $json->parameters->retry_after);
        }

        $this->logger->error(new Exception($response));

        return new Message(Message::STATUS_FAILED, $chat_id);
    }
}
