<?php

declare(strict_types=1);

namespace Common\Domain\Event;

interface EventDomainSubscriberInterface
{
    /**
     * @return array<string, array<string, int>>
     */
    public static function getSubscribedEvents(): array;
}
