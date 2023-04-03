<?php

declare(strict_types=1);

namespace Notification\Application\NotificationCreate\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class NotificationCreateOutputDto
{
    public function __construct(
        /**
         * @param Identifier[] $notificationIds
         */
        public readonly array $notificationIds
    ) {
    }
}
