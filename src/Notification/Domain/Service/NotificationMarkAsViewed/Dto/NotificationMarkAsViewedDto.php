<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationMarkAsViewed\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class NotificationMarkAsViewedDto
{
    /**
     * @param Identifier[] $notificationId
     */
    public function __construct(
        public readonly array $notificationId,
    ) {
    }
}
