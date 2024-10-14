<?php

declare(strict_types=1);

namespace Notification\Domain\Service\NotificationRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class NotificationRemoveDto
{
    /**
     * @param Identifier[] $notificationsId
     */
    public function __construct(
        public readonly array $notificationsId,
    ) {
    }
}
