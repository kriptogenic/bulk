<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

abstract class Controller
{
    protected function validateUuid(string $uuid)
    {
        $validator = Validator::make(['uuid' => $uuid], [
            'uuid' => ['required', 'uuid'],
        ]);

        if ($validator->fails()) {
            throw new BadRequestHttpException('Invalid UUID');
        }
    }
}
