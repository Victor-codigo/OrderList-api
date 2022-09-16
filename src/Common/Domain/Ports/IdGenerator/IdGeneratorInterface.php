<?php

declare(strict_types=1);

namespace Common\Domain\Ports\IdGenerator;

interface IdGeneratorInterface
{
    public static function createId(): string;
}
