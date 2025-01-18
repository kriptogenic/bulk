<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\TaskChatResource;
use App\Services\Repositories\TaskChatRepository;
use App\Services\Repositories\TaskRepository;

class TaskChatController extends Controller
{
    public function __construct(
        private TaskRepository $taskRepository,
        private TaskChatRepository $taskChatRepository,
    ) {}

    public function index(string $taskId)
    {
        $this->validateUuid($taskId);
        $task = $this->taskRepository->getById($taskId);

        $chats = $this->taskChatRepository->getChatsPaginate($task);

        return TaskChatResource::collection($chats);
    }
}
