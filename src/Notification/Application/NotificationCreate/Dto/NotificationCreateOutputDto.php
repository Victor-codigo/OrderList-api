<?php

declare(strict_types=1);

namespace Notification\Application\NotificationCreate\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class NotificationCreateOutputDto
{
    /**
     * @param Identifier[] $notificationIds
     */
    public function __construct(
        public readonly array $notificationIds,
    ) {
    }
}
