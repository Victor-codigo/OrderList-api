<?php

declare(strict_types=1);

namespace Notification\Application\NotificationGetData\Dto;

class NotificationGetDataOutputDto
{
    public function __construct(
        public readonly array $notificationsData
    ) {
    }
}
