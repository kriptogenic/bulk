<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SendMethod;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskRepository;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class TaskController extends Controller
{
    public function __construct(private TaskRepository $taskRepository) {}

    public function store(TaskStoreRequest $request)
    {
        $token = $request->validated('token');
        $method = SendMethod::from($request->validated('method'));
        $bot = new Nutgram($token);
        try {
            $botInfo = $bot->getMe();
            if ($botInfo === null) {
                throw new UnprocessableEntityHttpException('Wrong token');
            }
        } catch (TelegramException $e) {
            throw new UnprocessableEntityHttpException('Wrong token. Telegram exception: ' . $e->getMessage());
        }

        if ($this->taskRepository->hasPendingTaskForBot($botInfo->id)) {
            throw new UnprocessableEntityHttpException('Task already exists for this bot');
        }

        $task = $this->taskRepository->create(
            $botInfo->id,
            $botInfo->username,
            $token,
            $method,
            $request->validated('params'),
            $request->validated('chats'),
            $request->validated('webhook'),
        );

        return (new TaskResource($task))->additional([
            'message' => 'Task queued',
        ]);
    }

    public function show(string $id)
    {
        $this->validateUuid($id);
        $task = $this->taskRepository->getById($id);
        return new TaskResource($task);
    }
}
