<?php

declare(strict_types=1);

namespace Common\Adapter\Framework;

use Symfony\Component\Uid\Uuid;

class IdGenerator
{
    public static function createId(): string
    {
        return Uuid::v4()->toRfc4122();
    }
}
