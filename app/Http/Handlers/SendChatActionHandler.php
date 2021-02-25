<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Memory\TaskManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SendChatActionHandler
{
    private TaskManager $taskManager;

    public function __construct(ContainerInterface $container)
    {
        $this->taskManager = new TaskManager($container->get('redis_url'));
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {

    }
}
