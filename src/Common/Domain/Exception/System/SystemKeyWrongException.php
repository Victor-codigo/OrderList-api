<?php

declare(strict_types=1);

namespace Common\Domain\Exception\System;

use Common\Domain\Exception\DomainException;

class SystemKeyWrongException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
