<?php

declare(strict_types=1);

namespace App\Enums;

use RuntimeException;
use SergiX44\Nutgram\Telegram\Properties\ChatAction;

enum SendMethod: string
{
    case SendChatAction = 'sendChatAction';
    case SendMessage = 'sendMessage';

    /**
     * @return positive-int
     */
    public function perSecond(): int
    {
        return match ($this) {
            self::SendChatAction => 100,
            self::SendMessage => 10,
        };
    }

    public function prefetchAction(): ChatAction
    {
        return match ($this) {
            self::SendMessage => ChatAction::TYPING,
            self::SendChatAction => throw new RuntimeException('There is not prefetch action'),
        };
    }
}
