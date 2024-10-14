<?php

declare(strict_types=1);

namespace Notification\Application\NotificationRemove\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class NotificationRemoveOutputDto
{
    /**
     * @param Identifier[] $notificationsId
     */
    public function __construct(
        public readonly array $notificationsId,
    ) {
    }
}
