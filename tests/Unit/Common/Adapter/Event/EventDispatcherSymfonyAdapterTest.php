<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Event;

use Common\Adapter\Event\EventDispatcherSymfonyAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Test\Unit\Common\Adapter\Event\Fixtures\CustomEvent;
use Test\Unit\Common\Adapter\Event\Fixtures\CustomEventSubscriber;
use Test\Unit\Common\Adapter\Event\Fixtures\CustomEventSubscriberWithManyMethods;
use Test\Unit\Common\Adapter\Event\Fixtures\CustomEventSubscriberWithOneMethod;
use Test\Unit\Common\Adapter\Event\Fixtures\CustomEventSubscriberWithOneMethodWithPriority;

class EventDispatcherSymfonyAdapterTest extends TestCase
{
    private EventDispatcherSymfonyAdapter $object;
    private MockObject|EventDispatcher $eventDispatcher;

    public function setUp(): void
    {
        parent::setUp();

        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->object = new EventDispatcherSymfonyAdapter($this->eventDispatcher);
    }

    /** @test */
    public function addNewListener(): void
    {
        $eventName = 'MyEvent';
        $priority = 0;
        $listener = function () {};

        $this->eventDispatcher
            ->expects($this->once())
            ->method('addListener')
            ->with($eventName, $listener, $priority);

        $this->object->addListener($eventName, $listener, $priority);
    }

    /** @test */
    public function addNewSubscriberWithoutListenerDefined(): void
    {
        $subscriber = new CustomEventSubscriber();

        $this->eventDispatcher
            ->expects($this->once())
            ->method('addListener')
            ->with(CustomEvent::class, [$subscriber, '__invoke'], 0);

        $this->object->addSubscriber($subscriber);
    }

    /** @test */
    public function addNewSubscriberWithOneMethodDefinedNoPriority(): void
    {
        $subscriber = new CustomEventSubscriberWithOneMethod();

        $this->eventDispatcher
            ->expects($this->once())
            ->method('addListener')
            ->with(CustomEvent::class, [$subscriber, 'handler'], 0);

        $this->object->addSubscriber($subscriber);
    }

    /** @test */
    public function addNewSubscriberWithOneMethodDefinedWithPriority(): void
    {
        $subscriber = new CustomEventSubscriberWithOneMethodWithPriority();

        $this->eventDispatcher
            ->expects($this->once())
            ->method('addListener')
            ->with(CustomEvent::class, [$subscriber, 'handler'], 5);

        $this->object->addSubscriber($subscriber);
    }

    /** @test */
    public function addNewSubscriberWithManyMethodsDefined(): void
    {
        $subscriber = new CustomEventSubscriberWithManyMethods();

        $matcher = $this->exactly(5);
        $this->eventDispatcher
            ->expects($matcher)
            ->method('addListener')
            ->willReturnCallback(function (string $event, callable $callback, int $priority) use ($subscriber, $matcher) {
                $expectedCallNumber = $matcher->getInvocationCount();
                match ([$expectedCallNumber, $event, $callback, $priority]) {
                    [1, CustomEvent::class, [$subscriber, '__invoke'], 0],
                    [2, CustomEvent::class, [$subscriber, 'handler'], 0],
                    [3, CustomEvent::class, [$subscriber, 'handler'], 5],
                    [4, CustomEvent::class, [$subscriber, 'execute'], 10],
                    [5, CustomEvent::class, [$subscriber, 'exe'], 20] => null,
                    default => throw new \LogicException(sprintf('addListener withConsecutiveParameters Error: not match found. Call number[%d]', $expectedCallNumber))
                };
            });

        $this->object->addSubscriber($subscriber);
    }

    /** @test */
    public function dispatchEventNoEventSubscribers(): void
    {
        $event = new CustomEvent();

        $this->eventDispatcher
            ->expects($this->once())
            ->method('getListeners')
            ->with(CustomEvent::class)
            ->willReturn([]);

        $this->object->dispatch($event);
    }

    /** @test */
    public function dispatchEventWithEventSubscribers(): void
    {
        $event = new CustomEvent();
        $subscriber = new CustomEventSubscriber();

        $this->eventDispatcher
            ->expects($this->once())
            ->method('getListeners')
            ->with(CustomEvent::class)
            ->willReturn([$subscriber->__invoke(...)]);

        $this->object->dispatch($event);
    }
}
