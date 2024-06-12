<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationRemoveAllUserNotifications\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class NotificationRemoveAllUserNotificationsDto
{
    public function __construct(
        public readonly Identifier $userId
    ) {
    }
}
