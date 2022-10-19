<?php

declare(strict_types=1);

namespace User\Domain\Event\UserPreRegistered;

use Common\Domain\Event\EventDomainInterface;
use DateTimeImmutable;

class UserPreRegisteredEvent implements EventDomainInterface
{
    private DateTimeImmutable $occurredOn;

    public readonly string $id;
    public readonly string $emailTo;

    public function __construct(string $id, string $emailTo)
    {
        $this->id = $id;
        $this->emailTo = $emailTo;

        $this->occurredOn = new DateTimeImmutable();
    }

    public function getOccurreddOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
