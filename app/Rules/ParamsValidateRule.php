<?php

declare(strict_types=1);

namespace App\Rules;

use App\Enums\SendMethod;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use SergiX44\Nutgram\Telegram\Properties\ChatAction;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Message\LinkPreviewOptions;

class ParamsValidateRule implements DataAwareRule, ValidationRule
{
    private ?SendMethod $method = null;

    public function setData(array $data): void
    {
        $method = Arr::get($data, 'method');
        if (!is_string($method)) {
            return;
        }
        $this->method = SendMethod::tryFrom($method);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->method === null) {
            return;
        }

        if (!is_array($value)) {
            return;
        }

        $rules = match ($this->method) {
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
                ...$this->caption(),
                ...$this->parseMode(),
                ...$this->captionEntities(),
                ...$this->showCaptionAboveMedia(),
                ...$this->disableNotification(),
                ...$this->protectContent(),
                ...$this->replyMarkup(),
            ],
            SendMethod::ForwardMessage => [
                ...$this->fromChatId(),
                ...$this->messageId(),
                ...$this->disableNotification(),
                ...$this->protectContent(),
            ],
            SendMethod::SendPhoto => [
                ...$this->photo(),
                ...$this->caption(),
                ...$this->parseMode(),
                ...$this->captionEntities(),
                ...$this->showCaptionAboveMedia(),
                ...$this->hasSpoiler(),
                ...$this->disableNotification(),
                ...$this->protectContent(),
                ...$this->replyMarkup(),
            ],
        };
        $validator = Validator::make([
            'params' => $value,
        ], $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $fail($error);
            }
        }
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

    private function hasSpoiler(): array
    {
        return [
            'params.has_spoiler' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}
