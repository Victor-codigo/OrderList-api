<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationRemove\Dto;

class NotificationRemoveDto
{
    public function __construct(
        public readonly array $notificationsId
    ) {
    }
}
