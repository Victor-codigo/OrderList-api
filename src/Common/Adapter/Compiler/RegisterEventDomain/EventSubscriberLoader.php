<?php

declare(strict_types=1);

namespace Common\Adapter\Compiler\RegisterEventDomain;

use Common\Adapter\Event\EventDispatcherSymfonyAdapter;
use Common\Domain\Event\EventDomainSubscriberInterface;

class EventSubscriberLoader extends EventDispatcherSymfonyAdapter implements EventDomainSubscriberInterface
{
    /**
     * @var array<string, array<string|int>>
     */
    private array $listeners = [];
    private static string $subscriber;

    /**
     * @return array<string, array<string|int>>
     */
    public function getListeners(): array
    {
        return $this->listeners;
    }

    public function setSubscriber(string $subscriber): void
    {
        self::$subscriber = $subscriber;
    }

    /**
     * @param array<string, array<string|int>>|callable $listener
     */
    #[\Override]
    public function addListener(string $eventSubscriberName, array|callable $listener, int $priority = 0): void
    {
        $this->listeners[] = [$eventSubscriberName, $listener[1], $priority];
    }

    /**
     * @return array<string, array<string|int>>|string[]
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [self::$subscriber, 'getSubscribedEvents']();
    }
}
