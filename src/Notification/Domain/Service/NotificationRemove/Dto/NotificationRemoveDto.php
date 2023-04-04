<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationRemove\Dto;

use Notification\Domain\Model\Notification;

class NotificationRemoveDto
{
    /**
     * @param Notification[] $notifications
     */
    public function __construct(
        public readonly array $notificationsId
    ) {
    }
}
