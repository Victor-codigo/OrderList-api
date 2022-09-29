<?php

declare(strict_types=1);

namespace Common\Domain\Event;

use DateTimeImmutable;

interface EventDomainInterface
{
    public function getOccurreddOn(): DateTimeImmutable;
}
