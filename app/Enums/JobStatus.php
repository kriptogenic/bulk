<?php

declare(strict_types=1);

namespace App\Enums;

enum JobStatus: string
{
    case Pending = 'pending';
    case Reserved = 'reserved';
    case Completed = 'completed';
    case Failed = 'failed';
}
