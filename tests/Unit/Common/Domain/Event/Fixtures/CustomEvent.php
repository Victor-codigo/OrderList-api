<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Event\Fixtures;

use Common\Domain\Event\EventDomainInterface;

class CustomEvent implements EventDomainInterface
{
    public function __invoke(EventDomainInterface $event): void
    {
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
