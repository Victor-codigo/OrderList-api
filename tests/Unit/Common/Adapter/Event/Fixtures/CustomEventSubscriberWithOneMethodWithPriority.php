<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Event\Fixtures;

use Common\Domain\Event\EventDomainSubscriberInterface;

class CustomEventSubscriberWithOneMethodWithPriority implements EventDomainSubscriberInterface
{
    public function handler()
    {
    }

    /**
     * @return array<string, array<string|int>>
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            CustomEvent::class => ['handler', 5],
        ];
    }
}
