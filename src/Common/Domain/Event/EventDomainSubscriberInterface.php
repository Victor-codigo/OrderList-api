<?php

declare(strict_types=1);

namespace Common\Domain\Event;

interface EventDomainSubscriberInterface
{
    public static function getSubscribedEvents(): array;
}
