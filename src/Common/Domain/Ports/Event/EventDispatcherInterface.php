<?php

declare(strict_types=1);

namespace Common\Domain\Ports\Event;

use Common\Domain\Event\EventDomainInterface;
use Common\Domain\Event\EventDomainSubscriberInterface;

interface EventDispatcherInterface
{
    public function dispatch(EventDomainInterface $event): void;

    public function addSubscriber(EventDomainSubscriberInterface $subscriber): void;

    public function addListener(string $eventName, callable $listener, int $priority = 0): void;
}
