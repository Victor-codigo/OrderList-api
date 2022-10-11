<?php

declare(strict_types=1);

namespace Common\Domain\Exception;

use DomainException as NativeDomainException;
use Throwable;

class DomainException extends NativeDomainException
{
    public static function fromMessage(string $message = '', int $code = 0, Throwable|null $previous = null): static
    {
        return new static($message, $code, $previous);
    }
}
