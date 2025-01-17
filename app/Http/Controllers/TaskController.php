<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\SendMethod;
use App\Enums\TaskStatus;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Resources\TaskResource;
use App\Services\Repositories\TaskRepository;
use App\Services\TelegramService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class TaskController extends Controller
{
    public function __construct(private TaskRepository $taskRepository) {}

    public function store(TaskStoreRequest $request): TaskResource
    {
        $token = $request->validated('token');
        $method = SendMethod::from($request->validated('method'));
        $bot = new TelegramService($token);
        $botInfo = $bot->getMe();

        if ($this->taskRepository->hasPendingTaskForBot($botInfo->id)) {
            throw new BadRequestHttpException('Task already exists for this bot');
        }

        $prefetchAction = $bot->testMethodAndReturnPrefetchType(
            $method,
            $request->validated('test_chat_id'),
            $request->validated('params'),
        );

        $task = $this->taskRepository->create(
            $botInfo->id,
            $botInfo->username,
            $token,
            $method,
            $prefetchAction,
            $request->validated('params'),
            $request->validated('chats'),
            $request->validated('webhook'),
        );

        return new TaskResource($task)->additional([
            'message' => 'Task queued',
        ]);
    }

    public function show(string $id): TaskResource
    {
        $this->validateUuid($id);
        $task = $this->taskRepository->getById($id);
        return new TaskResource($task);
    }

    public function destroy(string $id): TaskResource
    {
        $this->validateUuid($id);
        $task = $this->taskRepository->getById($id);

        if (!in_array($task, [
            TaskStatus::Creating,
            TaskStatus::Pending,
            TaskStatus::InProgress,
        ], true)) {
            throw new BadRequestHttpException('Task can not be canceled.');
        }

        $this->taskRepository->setStatus($task, TaskStatus::Cancelled);
        return new TaskResource($task)->additional([
            'message' => 'Task cancelled successfully.',
        ]);
    }
}
