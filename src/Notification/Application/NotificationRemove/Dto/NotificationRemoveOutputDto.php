<?php

declare(strict_types=1);

namespace Notification\Application\NotificationRemove\Dto;

class NotificationRemoveOutputDto
{
    public function __construct(
        /**
         * @param Identifier[] $notificationsId
         */
        public readonly array $notificationsId
    ) {
    }
}
