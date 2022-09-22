<?php

declare(strict_types=1);

namespace Common\Domain\Event;

use Common\Domain\Ports\Event\EventDispatcherInterface;

trait EventDispatcherTrait
{
    /**
     * @param EventDomain[] $eventsRegistered
     */
    protected function eventsDispatch(EventDispatcherInterface $eventDispatcher, array ...$eventsRegistered): void
    {
        foreach (array_merge(...$eventsRegistered) as $eventData) {
            $eventDispatcher->dispatch($eventData);
        }
    }
}
