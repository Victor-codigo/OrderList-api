<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Event\Fixtures;

use Override;
use DateTimeImmutable;
use Common\Domain\Event\EventDomainInterface;

class CustomEvent implements EventDomainInterface
{
    public function __invoke(EventDomainInterface $event): void
    {
    }

    #[Override]
    public function getOccurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
