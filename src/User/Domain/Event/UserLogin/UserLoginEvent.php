<?php

declare(strict_types=1);

namespace User\Domain\Event\UserLogin;

use DateTimeImmutable;
use Override;
use Common\Domain\Event\EventDomainInterface;
use User\Domain\Model\User;

class UserLoginEvent implements EventDomainInterface
{
    private DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly User $user,
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }

    #[Override]
    public function getOccurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
