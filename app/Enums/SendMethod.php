<?php

declare(strict_types=1);

namespace App\Enums;

enum SendMethod: string
{
    case SendChatAction = 'sendChatAction';

    public function perSecond(): int
    {
        return 100;
    }
}
