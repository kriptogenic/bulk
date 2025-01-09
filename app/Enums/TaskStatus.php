<?php

declare(strict_types=1);

namespace App\Enums;

use MoonShine\Support\Enums\Color;

enum TaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function getColor(): Color
    {
        return match ($this) {
            self::Pending => Color::INFO,
            self::InProgress => Color::SECONDARY,
            self::Completed => Color::SUCCESS,
            self::Cancelled => Color::WARNING,
            self::Failed => Color::ERROR,
        };
    }
}
