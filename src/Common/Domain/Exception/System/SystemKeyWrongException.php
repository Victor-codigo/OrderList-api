<?php

declare(strict_types=1);

namespace App\Common\Domain\Exception\System;

use Common\Domain\Exception\DomainException;

class SystemKeyWrongException extends DomainException
{
    public static function formMessage(string $message): static
    {
        return new static($message);
    }
}
