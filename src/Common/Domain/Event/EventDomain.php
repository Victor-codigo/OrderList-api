<?php

declare(strict_types=1);

namespace Common\Domain\Event;

use DateTimeImmutable;

class EventDomain
{
    private readonly EventDomainInterface $data;
    private DateTimeImmutable $occurredOn;

    public function __construct(EventDomainInterface $eventData)
    {
        $this->data = $eventData;
        $this->occurredOn = new DateTimeImmutable();
    }

    public function __invoke()
    {
        $this->data->__invoke();
    }

    public function getOccurredOn()
    {
        return $this->occurredOn;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getName()
    {
        return $this->data::class;
    }
}
