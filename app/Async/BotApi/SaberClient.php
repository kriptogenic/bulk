<?php

namespace App\Async\BotApi;

use Psr\Log\LoggerInterface;
use Swlib\Http\ContentType;
use Swlib\Http\Exception\ClientException;
use Swlib\Saber;
use Throwable;

class SaberClient implements Client
{
    /**
     * @var string Telegram bot API endpoint
     */
//    private string $apiEndpoint = 'http://localhost:9501/';
    private string $apiEndpoint = 'https://api.telegram.org/';

    /**
     * @var Saber Saber HTTP client
     */
    private Saber $client;

    public function __construct(private LoggerInterface $logger)
    {
        $this->client = Saber::create([
            'base_uri' => $this->apiEndpoint,
            'content_type' => ContentType::JSON,
//            'exception_report' => HttpExceptionMask::E_ALL ^ HttpExceptionMask::E_CLIENT
        ]);
    }

    /**
     * @param string $token
     * @param string $method
     * @param int $chat_id
     * @param array $data
     * @return Message
     */
    public function execute(string $token, string $method, int $chat_id, array $data): Message
    {
        try {
            $this->client->request([
                'uri' => 'bot' . urlencode($token) . '/' . $method,
                'data' => $data + ['chat_id' => $chat_id],
                'method' => 'POST'
            ]);
//            $this->logger->info($res->getBody());
            return new Message(Message::STATUS_DELIVERED, $chat_id);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if (is_null($response)) {
                return $this->failMessage($chat_id, $e);
            }

            if ($response->statusCode === 403) {
                return new Message(Message::STATUS_NOT_DELIVERED, $chat_id);
            }

            if ($response->statusCode === 400) {
                if (str_contains($response->getBody(), 'chat not found')) {
                    return new Message(Message::STATUS_CHAT_NOT_FOUND, $chat_id);
                }
                if (str_contains($response->getBody(), 'have no rights')) {
                    return new Message(Message::STATUS_HAVE_NO_RIGHTS, $chat_id);
                }
            }

            if ($response->statusCode === 429) {
                $this->logger->warning($response->getBody());
                $this->logger->warning($chat_id);
                $retry = json_decode($response->getBody())->parameters->retry_after;
                return new Message(Message::STATUS_TOO_MANY_REQUESTS, $chat_id, $retry);
            }

            return $this->failMessage($chat_id, $response);
        } catch (Throwable $e) {
            return $this->failMessage($chat_id, $e);
        }
    }

    private function failMessage(int $chat_id, $exception): Message
    {
        $this->logger->error($chat_id);
        $this->logger->error($exception);
        return new Message(Message::STATUS_FAILED, $chat_id);
    }
}
