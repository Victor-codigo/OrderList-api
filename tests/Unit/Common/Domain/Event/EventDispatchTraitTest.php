<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Event;

use Common\Domain\Ports\Event\EventDispatcherInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Test\Unit\Common\Domain\Event\Fixtures\CustomEvent;
use Test\Unit\Common\Domain\Event\Fixtures\TraitClass;

class EventDispatchTraitTest extends TestCase
{
    private MockObject|TraitClass $object;
    private MockObject|EventDispatcherInterface $eventDispatcher;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->object = new TraitClass();

        $this->eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
    }

    /** @test */
    public function dispatchAnEvent(): void
    {
        $eventDomain = new CustomEvent();

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($eventDomain);

        $this->object->eventsRegisteredDispatch($this->eventDispatcher, [$eventDomain]);
    }

    /** @test */
    public function dispatchManyEvents(): void
    {
        $eventDomain1 = new CustomEvent();
        $eventDomain2 = new CustomEvent();
        $eventDomain3 = new CustomEvent();

        $matcher = $this->exactly(3);
        $this->eventDispatcher
            ->expects($matcher)
            ->method('dispatch')
            ->willReturnCallback(function (CustomEvent $event) use ($matcher, $eventDomain1, $eventDomain2, $eventDomain3): void {
                $expectedNumCall = $matcher->getInvocationCount();
                match ([$expectedNumCall, $event]) {
                    [1, $eventDomain1],
                    [2, $eventDomain2],
                    [3, $eventDomain3] => null,
                    default => throw new \LogicException('withConsecutive calls error')
                };
            });

        $this->object->eventsRegisteredDispatch($this->eventDispatcher, [$eventDomain1, $eventDomain2, $eventDomain3]);
    }

    /** @test */
    public function dispatchManyEventsDifferentArrayMerge(): void
    {
        $eventDomain1 = new CustomEvent();
        $eventDomain2 = new CustomEvent();
        $eventDomain3 = new CustomEvent();

        $eventDomain4 = new CustomEvent();
        $eventDomain5 = new CustomEvent();
        $eventDomain6 = new CustomEvent();

        $matcher = $this->exactly(6);
        $this->eventDispatcher
            ->expects($matcher)
            ->method('dispatch')
            ->willReturnCallback(function (CustomEvent $event) use ($matcher, $eventDomain1, $eventDomain2, $eventDomain3, $eventDomain4, $eventDomain5, $eventDomain6): void {
                match ([$matcher->getInvocationCount(), $event]) {
                    [1, $eventDomain1],
                    [2, $eventDomain2],
                    [3, $eventDomain3],
                    [4, $eventDomain4],
                    [5, $eventDomain5],
                    [6, $eventDomain6] => null,
                    default => throw new \LogicException('withConsecutive parameters exception')
                };
            });

        $this->object->eventsRegisteredDispatch($this->eventDispatcher, [$eventDomain1, $eventDomain2, $eventDomain3, $eventDomain4, $eventDomain5, $eventDomain6]);
    }
}
