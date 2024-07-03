<?php

declare(strict_types=1);

namespace Common\Domain\Exception;

use LogicException as NativeLogicException;

class LogicException extends NativeLogicException
{
    public static function fromMessage(string $message = '', int $code = 0, ?\Throwable $previous = null): static
    {
        return new static($message, $code, $previous);
    }
}
