<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Event\Fixtures;

use Common\Domain\Event\EventDomainSubscriberInterface;

class CustomEventSubscriber implements EventDomainSubscriberInterface
{
    public function __invoke()
    {
    }

    /**
     * @return string[]
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            CustomEvent::class,
        ];
    }
}
