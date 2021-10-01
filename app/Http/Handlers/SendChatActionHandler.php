<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Exceptions\ValidationException;
use App\Http\Rules\Enum;
use App\Http\TaskManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rules;
use Yiisoft\Validator\ValidatorInterface;

class SendChatActionHandler
{
    public function __construct(private TaskManager $taskManager, private ValidatorInterface $validator)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $request->getParsedBody();

        $action = $body['action'] ?? '';
        $this->validate($action);

        $this->taskManager->add($body['bot_id'], $body['token'], 'sendChatAction', 100,
                                ['action' => $action], $body['chats_id']);
        $response->getBody()->write(json_encode(['ok' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function validate($action)
    {
        $rules = new Rules([
            Required::rule(),
            HasLength::rule(),
            Enum::rule(['typing', 'upload_photo', 'record_video', 'upload_video', 'record_voice', 'upload_voice',
                         'upload_document', 'find_location', 'record_video_note', 'upload_video_note'])
        ]);
        //
        $result = $rules->validate($action);
        if (!$result->isValid()) {
            throw new ValidationException(['action' => $result->getErrors()]);
        }
    }
}
