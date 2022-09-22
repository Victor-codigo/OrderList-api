<?php

declare(strict_types=1);

namespace Common\Domain\Ports\Event;

use Common\Domain\Event\EventDomain;

interface EventDispatcherInterface
{
    public function dispatch(EventDomain $event): object;
}
