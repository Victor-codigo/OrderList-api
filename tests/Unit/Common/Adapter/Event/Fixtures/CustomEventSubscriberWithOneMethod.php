<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Event\Fixtures;

use Common\Domain\Event\EventDomainSubscriberInterface;

class CustomEventSubscriberWithOneMethod implements EventDomainSubscriberInterface
{
    public function handler()
    {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            CustomEvent::class => ['handler'],
        ];
    }
}
