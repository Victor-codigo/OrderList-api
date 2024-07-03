<?php

declare(strict_types=1);

namespace Notification\Application\NotificationRemoveAllUserNotifications\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class NotificationRemoveAllUserNotificationsOutputDto implements ApplicationOutputInterface
{
    /**
     * @param Identifier[] $notificationsRemovedId
     */
    public function __construct(
        public readonly array $notificationsRemovedId
    ) {
    }

    #[\Override]
    public function toArray(): array
    {
        return array_map(
            fn (Identifier $notificationId): ?string => $notificationId->getValue(),
            $this->notificationsRemovedId
        );
    }
}
