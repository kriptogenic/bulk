<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TaskFinishedEvent;
use App\Http\Resources\TaskResource;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;

class SendTaskWebhookListener
{
    public function __construct(private Client $httpClient) {}

    public function handle(TaskFinishedEvent $event): void
    {
        if ($event->task->webhook === null) {
            return;
        }

        try {
            $this->httpClient->post($event->task->webhook, [
                RequestOptions::JSON => new TaskResource($event->task),
            ]);

            Log::info('Webhook sending successfully.', [
                'task' => $event->task->id,
                'webhook' => $event->task->webhook,
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            Log::error('Webhook sending failed.', [
                'exception' => $e,
                'task' => $event->task->id,
                'webhook' => $event->task->webhook,
                'response_code' => $response->getStatusCode(),
                'response_body' => $response->getBody()->getContents(),
            ]);
        } catch (Exception $e) {
            Log::error('Webhook sending failed.', [
                'exception' => $e,
                'task' => $event->task->id,
                'webhook' => $event->task->webhook,
            ]);
        }
    }
}
