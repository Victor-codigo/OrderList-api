<?php

declare(strict_types=1);

namespace Notification\Domain\Model;

enum NOTIFICATION_TYPE: string
{
    case USER_REGISTERED = 'NOTIFICATION_USER_REGISTERED';
    case USER_EMAIL_CHANGED = 'NOTIFICATION_USER_EMAIL_CHANGED';
    case USER_PASSWORD_CHANGED = 'NOTIFICATION_USER_PASSWORD_CHANGED';
    case USER_PASSWORD_REMEMBER = 'NOTIFICATION_USER_PASSWORD_REMEMBER';
    case GROUP_CREATED = 'NOTIFICATION_GROUP_CREATED';
    case GROUP_USER_ADDED = 'NOTIFICATION_GROUP_USER_ADDED';
}
