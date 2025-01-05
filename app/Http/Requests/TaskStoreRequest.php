<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SendMethod;
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
            ],
            'chats.*' => [
                'required',
                'integer',
                'distinct',
            ],
            'webhook' => [
                'nullable',
                'string',
                'url',
            ],
            // @todo params
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
