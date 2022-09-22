<?php

declare(strict_types=1);

namespace Common\Domain\Event;

trait EventRegisterTrait
{
    /**
     * @var EventDomain[]
     */
    private array $eventsRegistered = [];

    protected function eventRegister(EventDomain $event): void
    {
        $this->eventsRegistered[] = $event;
    }

    public function getEventsRegistered(): array
    {
        return $this->eventsRegistered;
    }
}
