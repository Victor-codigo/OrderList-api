<?php

declare(strict_types=1);

namespace Common\Domain\Exception;

use DomainException;
use Throwable;

class DomainInternalErrorException extends DomainException
{
    public static function fromMessage(string $message = '', int $code = 0, Throwable|null $previous = null): static
    {
        return new static($message, $code, $previous);
    }
}
