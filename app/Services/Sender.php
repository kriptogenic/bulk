<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MessageStatus;
use App\Enums\SendMethod;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class Sender
{
    const HTTPS_API_TELEGRAM_ORG = 'https://api.telegram.org/';
    const ONE_SECOND = 1;
    const SECONDS_TO_MICROSECONDS = 1_000_000;

    public function __construct(private Client $client) {}

    /**
     * @param Collection<non-negative-int, int> $chats
     * @return Generator<Collection<int, MessageStatus>>
     */
    public function send(
        string $token,
        SendMethod $method,
        Collection $chats,
        array $params,
    ): Generator {
        $endpoint = self::HTTPS_API_TELEGRAM_ORG . 'bot' . $token . '/' . $method->value;
        foreach ($chats->chunk($method->perSecond()) as $chunkedIds) {
            yield $this->sendInInterval($chunkedIds, $endpoint, $params);
        }
    }

    /**
     * @return Collection<int, MessageStatus> key is chat_id
     */
    private function sendInInterval(Collection $chats, string $endpoint, array $params): Collection
    {
        $start = microtime(true);
        $retryAfter = 0;
        $result = collect();
        $requests = $chats
            ->mapWithKeys(fn(int $chatId)
                => [
                $chatId => new Request(
                    method: 'POST',
                    uri: $endpoint,
                    headers: [
                        'Content-Type' => 'application/json',
                    ],
                    body: json_encode([
                        ...$params,
                        'chat_id' => $chatId,
                    ]),
                ),
            ])
            ->getIterator();
        $pool = new Pool($this->client, $requests, [
            PromiseInterface::FULFILLED => function (ResponseInterface $response, int $chatId) use (&$result): void {
                $result->put($chatId, MessageStatus::Delivered);
            },
            PromiseInterface::REJECTED => function (RequestException $reason, int $chatId) use (
                &$result,
                &$retryAfter,
            ): void {
                $response = $reason->getResponse();
                if ($response === null) {
                    $result->put($chatId, MessageStatus::Failed);
                    report($reason);
                    return;
                }
                $contents = $response->getBody()->getContents();
                try {
                    $json = json_decode($contents, flags: JSON_THROW_ON_ERROR);
                } catch (Throwable $exception) {
                    $result->put($chatId, MessageStatus::Failed);
                    report($exception);
                    return;
                }

                if ($json->error_code === 403) {
                    $result->put($chatId, MessageStatus::Forbidden);
                    return;
                }

                if ($json->error_code === 400) {
                    if (str_contains($json->description, 'chat not found')) {
                        $result->put($chatId, MessageStatus::ChatNotFound);
                        return;
                    }
                    if (str_contains($json->description, 'have no rights')) {
                        $result->put($chatId, MessageStatus::HaveNoRights);
                        return;
                    }
                }

                if ($json->error_code === 429) {
                    Log::warning('Retry after ' . $json->parameters->retry_after, [
                        'chat_id' => $chatId,
                        'retry_after' => $json->parameters->retry_after,
                    ]);
                    $retryAfter = max($retryAfter, $json->parameters->retry_after);
                    $result->put($chatId, MessageStatus::TooManyRequests);
                    return;
                }

                $result->put($chatId, MessageStatus::Failed);
                Log::error(
                    'Failed to detect error type',
                    [
                        'response' => $contents,
                        'decodedJson' => $json,
                    ],
                );
            },
        ]);
        $pool->promise()->wait();

        if ($retryAfter > 0) {
            sleep($retryAfter);
        }

        $delta = microtime(true) - $start;
        Log::info('Delta: ' . $delta);
        if ($delta < self::ONE_SECOND) {
            $toSleep = intval((self::ONE_SECOND - $delta) * self::SECONDS_TO_MICROSECONDS);
            usleep($toSleep);
        }

        return $result;
    }
}
