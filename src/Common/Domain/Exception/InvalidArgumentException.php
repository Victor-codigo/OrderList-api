<?php

declare(strict_types=1);

namespace Common\Domain\Exception;

use InvalidArgumentException as NativeInvalidArgumentException;

class InvalidArgumentException extends NativeInvalidArgumentException
{
    public static function createFromMessage(string $message): static
    {
        return new static($message);
    }
}
