<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Event;

use Common\Domain\Event\EventDomain;
use Common\Domain\Ports\Event\EventDispatcherInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Test\Unit\Common\Domain\Event\Fixtures\CustomEvent;
use Test\Unit\Common\Domain\Event\Fixtures\TraitClass;
use stdClass;

class EventDispatchTraitTest extends TestCase
{
    private MockObject|TraitClass $object;
    private MockObject|EventDispatcherInterface $eventDispatcher;

    public function setUp(): void
    {
        parent::setUp();

        $this->object = new TraitClass();

        $this->eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
    }

    /** @test */
    public function dispatchAnEvent(): void
    {
        $eventDomain = new EventDomain(new CustomEvent());

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($eventDomain)
            ->willReturn(new stdClass());

        $this->object->eventsDispatch($this->eventDispatcher, [$eventDomain]);
    }

    /** @test */
    public function dispatchManyEvents(): void
    {
        $eventDomain1 = new EventDomain(new CustomEvent());
        $eventDomain2 = new EventDomain(new CustomEvent());
        $eventDomain3 = new EventDomain(new CustomEvent());

        $this->eventDispatcher
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->withConsecutive([$eventDomain1], [$eventDomain2], [$eventDomain3])
            ->willReturn(new stdClass());

        $this->object->eventsDispatch($this->eventDispatcher, [$eventDomain1, $eventDomain2, $eventDomain3]);

        $this->assertTrue(true);
    }

    /** @test */
    public function dispatchManyEventsDifferentArrayMerge(): void
    {
        $eventDomain1 = new EventDomain(new CustomEvent());
        $eventDomain2 = new EventDomain(new CustomEvent());
        $eventDomain3 = new EventDomain(new CustomEvent());

        $eventDomain4 = new EventDomain(new CustomEvent());
        $eventDomain5 = new EventDomain(new CustomEvent());
        $eventDomain6 = new EventDomain(new CustomEvent());

        $this->eventDispatcher
            ->expects($this->exactly(6))
            ->method('dispatch')
            ->withConsecutive(
                [$eventDomain1],
                [$eventDomain2],
                [$eventDomain3],
                [$eventDomain4],
                [$eventDomain5],
                [$eventDomain6],
            )
            ->willReturn(new stdClass());

        $this->object->eventsDispatch($this->eventDispatcher, [$eventDomain1, $eventDomain2, $eventDomain3, $eventDomain4, $eventDomain5, $eventDomain6]);
    }
}
