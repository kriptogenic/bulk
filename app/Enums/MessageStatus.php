<?php

declare(strict_types=1);

namespace App\Enums;

use MoonShine\Support\Enums\Color;

enum MessageStatus: string
{
    case Delivered = 'delivered';
    case Forbidden = 'forbidden';
    case ChatNotFound = 'chat_not_found';
    case Failed = 'failed';
    case TooManyRequests = 'to_many_requests';
    case HaveNoRights = 'have_no_rights';
    case Pending = 'pending';

    public function getColor(): Color
    {
        return match ($this) {
            self::Delivered => Color::SUCCESS,
            self::Forbidden, self::Failed, self::ChatNotFound, self::HaveNoRights => Color::ERROR,
            self::TooManyRequests => Color::WARNING,
            self::Pending => Color::GRAY,
        };
    }
}
