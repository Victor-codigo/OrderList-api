<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationMarkAsViewed\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class NotificationMarkAsViewedDto
{
    public function __construct(
        /**
         * @param Identifier[] $notificationId
         */
        public readonly array $notificationId,
    ) {
    }
}
