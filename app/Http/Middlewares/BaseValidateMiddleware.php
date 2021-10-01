<?php

declare(strict_types=1);

namespace App\Http\Middlewares;

use App\Http\Exceptions\ApiException;
use App\Http\Exceptions\InvalidTokenException;
use App\Http\Exceptions\ValidationException;
use App\Http\Rules\ArrayRule;
use App\Http\TaskManager;
use App\Http\TelegramApi;
use App\NullableDataSet;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Validator\Rule\Each;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rules;
use Yiisoft\Validator\ValidatorInterface;

class BaseValidateMiddleware implements MiddlewareInterface
{
    public function __construct(private ValidatorInterface $validator,
        private TelegramApi $api,
        private TaskManager $taskManager)
    {
    }

    /**
     * @throws ApiException
     * @throws InvalidTokenException
     * @throws ValidationException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $body = $request->getParsedBody();

        $this->validate($body);
        $bot_id = $this->getBotId($body['token']);
        $this->checkDuplicationTask($bot_id);

        return $handler->handle($request->withParsedBody($body + ['bot_id' => $bot_id]));
    }

    /**
     * @throws ValidationException
     */
    private function validate(array $body)
    {
        $results = $this->validator->validate(new NullableDataSet($body), [
            'token' => [
                Required::rule(),
                HasLength::rule()
            ],
            'chats_id' => [
                Required::rule(),
                ArrayRule::rule()->unique()->max(10_000),
                Each::rule(new Rules([Number::rule()->integer()])),
            ]
        ]);

        if(!$results->isValid()) {
            throw new ValidationException($results->getErrors());
        }
    }

    /**
     * @throws InvalidTokenException
     */
    private function getBotId(string $token): int
    {
        $me = $this->api->getMe($token);

        if (!$me['ok']) {
            throw new InvalidTokenException($me);
        }

        return $me['result']['id'];
    }

    /**
     * @throws ApiException
     */
    private function checkDuplicationTask(int $bot_id)
    {
        if ($this->taskManager->exists($bot_id)) {
            throw new ApiException('Task already exists for this bot', 429);
        }
    }
}
