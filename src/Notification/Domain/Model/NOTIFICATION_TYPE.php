<?php

declare(strict_types=1);

namespace Notification\Domain\Model;

enum NOTIFICATION_TYPE: string
{
    case USER_REGISTERED = 'NOTIFICATION_USER_REGISTERED';
    case GROUP_USER_ADDED = 'NOTIFICATION_GROUP_USER_ADDED';
}
