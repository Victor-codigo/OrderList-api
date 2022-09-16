<?php

declare(strict_types=1);

namespace Common\Adapter\IdGenerator;

use Common\Domain\Ports\IdGenerator\IdGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class IdGenerator implements IdGeneratorInterface
{
    public static function createId(): string
    {
        return Uuid::v4()->toRfc4122();
    }
}
