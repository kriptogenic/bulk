<?php

declare(strict_types=1);

namespace App\Http\Handlers;

use App\Http\Exceptions\ApiException;
use App\Http\Exceptions\ValidationException;
use App\Http\TaskManager;
use App\Http\TelegramApiCallTester;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Valitron\Validator;

class SendMessageHandler
{
    public function __construct(private TaskManager $redis, private TelegramApiCallTester $apiCallTester)
    {
    }

    /**
     * @throws ApiException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $request->getParsedBody();

        $data = array_only_keys($body, ['text', 'parse_mode', 'disable_web_page_preview',
            'disable_notification', 'entities', 'reply_markup']);
        $this->validate($data);

        $this->apiCallTester->sendMessage($data);

        $chats_id = array_unique($body['chats_id']);

        $this->redis->add($body['bot_id'], $body['token'], 'sendMessage', 10, $data, $chats_id);

        $response->getBody()->write('dd');
        return $response;
    }

    /**
     * @throws ValidationException
     */
    private function validate(array $body)
    {
        $validator = new Validator($body);
        $validator->setPrependLabels(false);

        $validator->rule('required', 'text')
            ->rule('string', ['text', 'parse_mode'])
            ->rule('lengthMax', 'text', 4096)
            ->rule('parse_mode_values', 'parse_mode')
            ->rule('exclude_if_entities', 'parse_mode')
            ->rule('boolean', ['disable_web_page_preview', 'disable_notification'])
            ->rule('my_array', 'entities');

        if (!$validator->validate()) {
            throw new ValidationException($validator->errors());
        }
    }
}
