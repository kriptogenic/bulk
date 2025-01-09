<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SendMethod;
use Exception;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Hydrator\NutgramHydrator;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Exceptions\TelegramException;
use SergiX44\Nutgram\Telegram\Properties\ChatAction;
use SergiX44\Nutgram\Telegram\Properties\MessageType;
use SergiX44\Nutgram\Telegram\Types\BaseType;
use SergiX44\Nutgram\Telegram\Types\Message\Message;
use SergiX44\Nutgram\Telegram\Types\User\User;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TelegramService
{
    private Nutgram $bot;

    public function __construct(string $token)
    {
        $this->bot = new Nutgram($token);
    }

    public function getMe(): User
    {
        try {
            $botInfo = $this->bot->getMe();
            if ($botInfo === null) {
                throw new BadRequestHttpException('Wrong token');
            }
            return $botInfo;
        } catch (TelegramException $e) {
            throw new BadRequestHttpException('Wrong token. Telegram exception: ' . $e->getMessage());
        }
    }

    public function testMethodAndReturnPrefetchType(SendMethod $method, int $chatId, array $params): ?ChatAction
    {
        $message = $this->testMessage($method, $params, $chatId);
        if ($message === null) {
            return null;
        }
        return $method->prefetchAction() ?? $this->prefetchByMessage($message->getType());
    }

    private function testMessage(SendMethod $method, array $params, int $chatId): ?Message
    {
        try {
            $unknownType = $this->bot->sendRequest($method->value, [
                ...$params,
                'chat_id' => $chatId,
            ]);

            if ($method === SendMethod::SendChatAction) {
                return null;
            }

            if ($method === SendMethod::CopyMessage) {
                return $this->bot->forwardMessage($chatId, $params['from_chat_id'], $params['message_id']);
            }
        } catch (TelegramException $e) {
            throw new BadRequestHttpException('Wrong parameters. Telegram exception: ' . $e->getMessage());
        }

        if (!$unknownType instanceof BaseType) {
            Log::error('Unexpected type: ', [
                'response' => $unknownType,
            ]);
            throw new Exception('Unexpected response from telegram');
        }

        return app(NutgramHydrator::class)->hydrate($unknownType->toArray(), Message::class);
    }

    private function prefetchByMessage(?MessageType $messageType): ChatAction
    {
        if ($messageType === null) {
            throw new Exception('Prefetch type mismatch');
        }

        return match ($messageType) {
            MessageType::TEXT,
            MessageType::CONTACT,
            MessageType::DICE,
            MessageType::POLL => ChatAction::TYPING,
            MessageType::ANIMATION,
            MessageType::VIDEO => ChatAction::UPLOAD_VIDEO,
            MessageType::AUDIO,
            MessageType::VOICE => ChatAction::UPLOAD_VOICE,
            MessageType::DOCUMENT => ChatAction::UPLOAD_DOCUMENT,
            MessageType::PHOTO => ChatAction::UPLOAD_PHOTO,
            MessageType::STICKER => ChatAction::CHOOSE_STICKER,
            MessageType::VIDEO_NOTE => ChatAction::UPLOAD_VIDEO_NOTE,
            MessageType::VENUE,
            MessageType::LOCATION => ChatAction::FIND_LOCATION,
            default => throw new Exception('Prefetch type mismatch:' . $messageType->value),
        };
    }
}
