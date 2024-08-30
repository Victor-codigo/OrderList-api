<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Test\Unit\Common\Domain\Event\Fixtures\CustomEvent;
use Test\Unit\Common\Domain\Event\Fixtures\TraitClass;

class EventRegisterTraitTest extends TestCase
{
    private TraitClass $object;
    private CustomEvent $event;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->object = new TraitClass();
        $this->event = new CustomEvent();
    }

    #[Test]
    public function getEventsRegistered(): void
    {
        $return = $this->object->getEventsRegistered();

        $this->assertEquals([], $return);
    }

    #[Test]
    public function registerNewEvent(): void
    {
        $eventDomain1 = $this->event;
        $eventDomain2 = $this->event;
        $this->object->eventDispatchRegister($eventDomain1);
        $this->object->eventDispatchRegister($eventDomain2);

        $this->assertEquals([$eventDomain1, $eventDomain2], $this->object->getEventsRegistered());
    }
}
