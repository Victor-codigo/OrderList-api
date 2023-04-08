<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationCreate\Dto;

use Common\Domain\Model\ValueObject\Array\NotificationData;
use Common\Domain\Model\ValueObject\Object\NotificationType;
use Common\Domain\Model\ValueObject\String\Identifier;

class NotificationCreateDto
{
    public function __construct(
        /**
         * @param Identifier[] $usersId
         */
        public readonly array $usersId,
        public readonly NotificationType $notificationType,
        public readonly NotificationData $notificationData
    ) {
    }
}
