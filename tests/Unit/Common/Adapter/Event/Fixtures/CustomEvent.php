<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Event\Fixtures;

use Common\Domain\Event\EventDomainInterface;

class CustomEvent implements EventDomainInterface
{
    public function getOccurredOn(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
