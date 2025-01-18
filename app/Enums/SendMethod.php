<?php

declare(strict_types=1);

namespace App\Enums;

use RuntimeException;
use SergiX44\Nutgram\Telegram\Properties\ChatAction;

enum SendMethod: string
{
    case SendChatAction = 'sendChatAction';
    case SendMessage = 'sendMessage';
    case CopyMessage = 'copyMessage';
    case ForwardMessage = 'forwardMessage';
    case SendPhoto = 'sendPhoto';
    case SendVideo = 'sendVideo';
    case SendAnimation = 'sendAnimation';
    case SendAudio = 'sendAudio';
    case SendVoice = 'sendVoice';
    case SendVideoNote = 'sendVideoNote';
    case SendDocument = 'sendDocument';

    /**
     * @return positive-int
     */
    public function perSecond(): int
    {
        return match ($this) {
            self::SendChatAction => 100,
            default => 10,
        };
    }

    public function prefetchAction(): ?ChatAction
    {
        return match ($this) {
            self::SendMessage => ChatAction::TYPING,
            self::SendPhoto => ChatAction::UPLOAD_PHOTO,
            self::SendVideo, self::SendAnimation => ChatAction::UPLOAD_VIDEO,
            self::SendAudio, self::SendVoice => ChatAction::UPLOAD_VOICE,
            self::SendVideoNote => ChatAction::UPLOAD_VIDEO_NOTE,
            self::SendDocument => ChatAction::UPLOAD_DOCUMENT,
            self::CopyMessage, self::ForwardMessage => null,
            self::SendChatAction => throw new RuntimeException('There is not prefetch action'),
        };
    }
}
