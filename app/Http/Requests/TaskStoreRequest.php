<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SendMethod;
use App\Rules\FastArrayIntegerDistinctRule;
use App\Rules\ParamsValidateRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TaskStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'token' => [
                'required',
                'string',
                'regex:/^[0-9]{4,10}:[a-zA-Z0-9_-]{35}$/',
            ],
            'method' => [
                'required',
                'string',
                Rule::enum(SendMethod::class),
            ],
            'chats' => [
                'required',
                'array',
                'min:1',
                new FastArrayIntegerDistinctRule(),
            ],
            'params' => [
                'required',
                'array',
                new ParamsValidateRule(),
            ],
            'test_chat_id' => [
                'required',
                'integer',
            ],
            'webhook' => [
                'nullable',
                'string',
                'url',
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
