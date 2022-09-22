<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Event;

use Common\Domain\Event\EventDomain;
use PHPUnit\Framework\TestCase;
use Test\Unit\Common\Domain\Event\Fixtures\CustomEvent;
use Test\Unit\Common\Domain\Event\Fixtures\TraitClass;

class EventRegisterTraitTest extends TestCase
{
    private TraitClass $object;
    private CustomEvent $event;

    public function setUp(): void
    {
        parent::setUp();

        $this->object = new TraitClass();
        $this->event = new CustomEvent();
    }

    /** @test */
    public function getEventsRegistered()
    {
        $return = $this->object->getEventsRegistered();

        $this->assertEquals([], $return);
    }

    /** @test */
    public function registerNewEvent(): void
    {
        $eventDomain1 = new EventDomain($this->event);
        $eventDomain2 = new EventDomain($this->event);
        $this->object->eventRegister($eventDomain1);
        $this->object->eventRegister($eventDomain2);

        $this->assertEquals([$eventDomain1, $eventDomain2], $this->object->getEventsRegistered());
    }
}
