<?php

declare(strict_types=1);

namespace Notification\Application\NotificationMarkAsViewed\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class NotificationMarkAsViewedOutputDto implements ApplicationOutputInterface
{
    /**
     * @param Identifier[] $notificationIds
     */
    public function __construct(
        public readonly array $notificationIds,
    ) {
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function toArray(): array
    {
        return array_map(
            fn (Identifier $notificationId): ?string => $notificationId->getValue(),
            $this->notificationIds
        );
    }
}
