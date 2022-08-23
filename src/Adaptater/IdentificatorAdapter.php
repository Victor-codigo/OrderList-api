<?php

declare(strict_types=1);

namespace App\Adaptater;

use Symfony\Component\Uid\Uuid;

class IdentificatorAdapter
{
    public static function createId(): string
    {
        return Uuid::v4()->toRfc4122();
    }
}
