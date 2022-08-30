<?php

declare(strict_types=1);

namespace Common\Domain\Exception;

use LogicException as NativeLogicException;

class LogicException extends NativeLogicException
{
    public static function createFromMessage(string $message): static
    {
        return new static($message);
    }
}
