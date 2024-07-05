<?php

declare(strict_types=1);

namespace Common\Adapter\Compiler\RegisterEventDomain;

use Common\Adapter\Event\EventDispatcherSymfonyAdapter;
use Common\Domain\Event\EventDomainSubscriberInterface;

class EventSubscriberLoader extends EventDispatcherSymfonyAdapter implements EventDomainSubscriberInterface
{
    private array $listeners = [];
    private static string $subscriber;

    public function getListeners(): array
    {
        return $this->listeners;
    }

    public function setSubscriber(string $subscriber): void
    {
        static::$subscriber = $subscriber;
    }

    #[\Override]
    public function addListener(string $eventSubscriberName, array|callable $listener, int $priority = 0): void
    {
        $this->listeners[] = [$eventSubscriberName, $listener[1], $priority];
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [static::$subscriber, 'getSubscribedEvents']();
    }
}
