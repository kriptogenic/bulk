<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SendMethod;
use Illuminate\Validation\Rule;
use SergiX44\Nutgram\Telegram\Properties\ChatAction;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Message\LinkPreviewOptions;

class SendMethodRules
{
    public function match(SendMethod $method): array
    {
        return match ($method) {
            SendMethod::SendChatAction => [
                'params.action' => [
                    'required',
                    'string',
                    Rule::enum(ChatAction::class),
                ],
            ],
            SendMethod::SendMessage => [
                ...$this->text(),
                ...$this->parseMode(),
                ...$this->entities(),
                ...$this->linkPreviewOptions(),
                ...$this->disableNotification(),
                ...$this->protectContent(),
                ...$this->replyMarkup(),
            ],
            SendMethod::CopyMessage => [
                ...$this->fromChatId(),
                ...$this->messageId(),
                ...$this->baseAttachment(),
                ...$this->showCaptionAboveMedia(),
            ],
            SendMethod::ForwardMessage => [
                ...$this->fromChatId(),
                ...$this->messageId(),
                ...$this->disableNotification(),
                ...$this->protectContent(),
            ],
            SendMethod::SendPhoto => [
                ...$this->photo(),
                ...$this->baseAttachment(),
                ...$this->showCaptionAboveMedia(),
                ...$this->hasSpoiler(),
            ],
            SendMethod::SendVideo => [
                ...$this->video(),
                ...$this->baseAttachment(),
                ...$this->showCaptionAboveMedia(),
                ...$this->hasSpoiler(),
            ],
            SendMethod::SendAnimation => [
                ...$this->animation(),
                ...$this->baseAttachment(),
                ...$this->showCaptionAboveMedia(),
                ...$this->hasSpoiler(),
            ],
            SendMethod::SendAudio => [
                ...$this->audio(),
                ...$this->baseAttachment(),
            ],
            SendMethod::SendVoice => [
                ...$this->voice(),
                ...$this->baseAttachment(),
            ],
            SendMethod::SendVideoNote => [
                ...$this->videoNote(),
                ...$this->baseAttachment(),
            ],
            SendMethod::SendDocument => [
                ...$this->document(),
                ...$this->baseAttachment(),
            ],
        };
    }

    private function text(bool $isCaption = false): array
    {
        $key = $isCaption ? 'caption' : 'text';
        $length = $isCaption ? 1024 : 4096;
        return [
            'params.' . $key => [
                'nullable',
                'string',
                'min:1',
                'max:' . $length,
            ],
        ];
    }

    private function caption(): array
    {
        return $this->text(true);
    }

    private function parseMode(): array
    {
        return [
            'params.parse_mode' => [
                'nullable',
                'string',
                Rule::enum(ParseMode::class),
            ],
        ];
    }

    private function entities(bool $isCaption = false): array
    {
        $key = $isCaption ? 'caption_entities' : 'entities';
        return [
            'params.' . $key => [
                'nullable',
                'prohibited_if:params.parse_mode',
                'array',
            ],
        ];
    }

    private function captionEntities(): array
    {
        return $this->entities(true);
    }

    private function linkPreviewOptions(): array
    {
        return [
            'params.link_preview_options' => [
                'nullable',
                'string',
                Rule::enum(LinkPreviewOptions::class),
            ],
        ];
    }

    private function disableNotification(): array
    {
        return [
            'params.disable_notification' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    private function protectContent(): array
    {
        return [
            'params.protect_content' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    private function replyMarkup(): array
    {
        return [
            'params.reply_markup' => [
                'nullable',
                // @todo rules
            ],
        ];
    }

    private function fromChatId(): array
    {
        return [
            'params.from_chat_id' => [
                'required',
                'integer',
            ],
        ];
    }

    private function messageId(): array
    {
        return [
            'params.message_id' => [
                'required',
                'integer',
            ],
        ];
    }

    private function showCaptionAboveMedia(): array
    {
        return [
            'params.show_caption_above_media' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    private function hasSpoiler(): array
    {
        return [
            'params.has_spoiler' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    private function file(string $field): array
    {
        return [
            'params.' . $field => [
                'required',
                'string',
                'max:100',
            ],
        ];
    }

    private function photo(): array
    {
        return $this->file('photo');
    }

    private function video(): array
    {
        return $this->file('video');
    }

    private function animation(): array
    {
        return $this->file('animation');
    }

    private function audio(): array
    {
        return $this->file('audio');
    }

    private function voice(): array
    {
        return $this->file('voice');
    }

    private function videoNote(): array
    {
        return $this->file('video_note');
    }

    private function document(): array
    {
        return $this->file('document');
    }

    private function baseAttachment(): array
    {
        return [
            ...$this->caption(),
            ...$this->parseMode(),
            ...$this->captionEntities(),
            ...$this->disableNotification(),
            ...$this->protectContent(),
            ...$this->replyMarkup(),
        ];
    }
}
