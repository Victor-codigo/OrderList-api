<?php

declare(strict_types=1);

namespace Common\Domain\Event;

interface EventDomainSubscriberInterface
{
    /**
     * @return array<int|string, string|array<int, int|string>>
     */
    public static function getSubscribedEvents(): array;
}
