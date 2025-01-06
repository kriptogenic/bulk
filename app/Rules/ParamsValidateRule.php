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
use SergiX44\Nutgram\Telegram\Properties\MessageEntityType;
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
                'params.text' => [
                    'required',
                    'string',
                    'min:1',
                    'max:4096',
                ],
                'params.parse_mode' => [
                    'nullable',
                    'string',
                    Rule::enum(ParseMode::class),
                ],
                'params.entities' => [
                    'prohibited_if:params.parse_mode',
                    'array',
                ],
                'params.entities.*.type' => [
                    'required',
                    'string',
                    Rule::enum(MessageEntityType::class),
                ],
                'params.entities.*.offset' => [
                    'required',
                    'integer',
                ],
                'params.entities.*.length' => [
                    'required',
                    'integer',
                ],
                'params.entities.*.url' => [
                    'required_if:params.entities.*.type,' . MessageEntityType::TEXT_LINK->value,
                    'prohibited_unless:params.entities.*.type,' . MessageEntityType::TEXT_LINK->value,
                    'string',
                    'url',
                ],
                'params.entities.*.language' => [
                    'required_if:params.entities.*.type,' . MessageEntityType::PRE->value,
                    'prohibited_unless:params.entities.*.type,' . MessageEntityType::PRE->value,
                    'string',
                ],
                'params.entities.*.custom_emoji_id' => [
                    'required_if:params.entities.*.type,' . MessageEntityType::CUSTOM_EMOJI->value,
                    'prohibited_unless:params.entities.*.type,' . MessageEntityType::CUSTOM_EMOJI->value,
                    'string',
                ],
                'params.link_preview_options' => [
                    'nullable',
                    'string',
                    Rule::enum(LinkPreviewOptions::class),
                ],
                'params.disable_notification' => [
                    'nullable',
                    'boolean',
                ],
                'params.protect_content' => [
                    'nullable',
                    'boolean',
                ],
                'params.reply_markup' => [
                    'nullable',
                    // @todo rules
                ],
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
}
