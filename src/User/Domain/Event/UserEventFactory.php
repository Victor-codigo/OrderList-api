<?php

declare(strict_types=1);

namespace User\Domain\Event;

use Common\Domain\Event\EventDomain;
use Common\Domain\Event\EventDomainInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class UserEventFactory
{
    public static function createUserPreRegisteredEvent(Identifier $id): EventDomain
    {
        return self::createEventDomain(new UserPreRegisteredEvent($id));
    }

    private static function createEventDomain(EventDomainInterface $event)
    {
        return new EventDomain($event);
    }
}
