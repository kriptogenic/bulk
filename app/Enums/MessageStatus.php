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
    case PeerIdInvalid = 'peer_id_invalid';
    case Pending = 'pending';

    public function getColor(): Color
    {
        return match ($this) {
            self::Delivered => Color::SUCCESS,
            self::Forbidden, self::Failed, self::ChatNotFound, self::HaveNoRights, self::PeerIdInvalid => Color::ERROR,
            self::TooManyRequests => Color::WARNING,
            self::Pending => Color::SECONDARY,
        };
    }

    public function getHexColor(): string
    {
        return match ($this) {
            self::Delivered => '#28a745', // Green
            self::Forbidden => '#dc3545', // Red
            self::ChatNotFound, self::PeerIdInvalid => '#964B00', // Gray
            self::Failed => '#ff073a', // Bright Red
            self::TooManyRequests => '#ffc107', // Yellow
            self::HaveNoRights => '#17a2b8', // Cyan
            self::Pending => '#6c757d', // Blue
        };
    }
}
