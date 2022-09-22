<?php

declare(strict_types=1);

namespace Common\Adapter\Event;

use Common\Domain\Event\EventDomain;
use Common\Domain\Ports\Event\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

class EventDispatcherSymfonyAdapter implements EventDispatcherInterface
{
    private SymfonyEventDispatcherInterface $eventDispatcher;

    public function __construct(SymfonyEventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function dispatch(EventDomain $event): object
    {
        return $this->eventDispatcher->dispatch($event);
    }
}
