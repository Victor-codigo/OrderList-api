<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Event\Fixtures;

use Common\Domain\Event\EventDomainInterface;
use DateTimeImmutable;

class CustomEvent implements EventDomainInterface
{
    public function getOccurreddOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
