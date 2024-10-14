<?php

declare(strict_types=1);

namespace Common\Adapter\Event;

use Common\Domain\Event\EventDomainInterface;
use Common\Domain\Event\EventDomainSubscriberInterface;
use Common\Domain\Ports\Event\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;

class EventDispatcherSymfonyAdapter implements EventDispatcherInterface
{
    private SymfonyEventDispatcherInterface $eventDispatcher;

    public function __construct(SymfonyEventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    #[\Override]
    public function dispatch(EventDomainInterface $event): void
    {
        $listeners = $this->eventDispatcher->getListeners($event::class);

        foreach ($listeners as $listener) {
            $listener($event);
        }
    }

    #[\Override]
    public function addSubscriber(EventDomainSubscriberInterface $subscriber): void
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $eventParams) {
            if (is_string($eventParams)) {
                $eventName = $eventParams;
                $eventParams = ['__invoke', 0];
            }

            // @phpstan-ignore function.impossibleType
            if (!is_array($eventParams[0])) {
                $eventParams = [$eventParams];
            }

            $this->setSubscriberListeners($subscriber, $eventName, $eventParams);
        }
    }

    /**
     * @param string[]|callable $listener
     */
    #[\Override]
    public function addListener(string $eventName, array|callable $listener, int $priority = 0): void
    {
        $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * @param array<int|string, string|array<int, int|string>> $eventParams
     */
    private function setSubscriberListeners(EventDomainSubscriberInterface $subscriber, string $eventName, array $eventParams): void
    {
        foreach ($eventParams as $params) {
            $this->addListener($eventName, [$subscriber, $params[0]], $params[1] ?? 0);
        }
    }
}
