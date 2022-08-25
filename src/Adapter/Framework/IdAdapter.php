<?php

declare(strict_types=1);

namespace Adapter\Framework;

use Symfony\Component\Uid\Uuid;

class IdAdapter
{
    public static function createId(): string
    {
        return Uuid::v4()->toRfc4122();
    }
}
