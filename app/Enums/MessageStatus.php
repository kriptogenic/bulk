<?php

declare(strict_types=1);

namespace App\Enums;

enum MessageStatus: string
{
    case Delivered = 'delivered';
    case NotDelivered = 'not_delivered';
    case ChatNotFound = 'chat_not_found';
    case Failed = 'failed';
    case TooManyRequests = 'to_many_requests';
    case HaveNoRights = 'have_no_rights';
    case Pending = 'pending';
}
