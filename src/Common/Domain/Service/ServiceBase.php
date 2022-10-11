<?php

declare(strict_types=1);

namespace Common\Domain\Service;

use Common\Domain\Event\EventDispatcherTrait;

abstract class ServiceBase
{
    use EventDispatcherTrait;
}
