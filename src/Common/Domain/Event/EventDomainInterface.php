<?php

declare(strict_types=1);

namespace Common\Domain\Event;

interface EventDomainInterface
{
    public function getOccurredOn(): \DateTimeImmutable;
}
