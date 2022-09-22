<?php

declare(strict_types=1);

namespace Common\Adapter\Event;

use Common\Domain\Event\EventDomain;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class EventListenerSymfonyAdapter
{
    public function __invoke(EventDomain $event)
    {
        $event->__invoke();
    }
}
