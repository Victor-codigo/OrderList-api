<?php

declare(strict_types=1);

namespace Notification\Application\NotificationMarkAsViewed\Dto;

use Override;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class NotificationMarkAsViewedOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        /**
         * @param Identifier[] $notificationIds
         */
        public readonly array $notificationIds
    ) {
    }

    #[Override]
    public function toArray(): array
    {
        return array_map(
            fn (Identifier $notificationId): ?string => $notificationId->getValue(),
            $this->notificationIds
        );
    }
}
