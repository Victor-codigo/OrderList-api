<?php

declare(strict_types=1);

namespace Test\Unit\Common\Domain\Event\Fixtures;

use Common\Domain\Event\EventDispatcherTrait;
use Common\Domain\Event\EventRegisterTrait;

class TraitClass
{
    use EventRegisterTrait{
        eventDispatchRegister as public;
    }
    use EventDispatcherTrait{
        eventsRegisteredDispatch as public;
    }
}
