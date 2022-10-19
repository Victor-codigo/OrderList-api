<?php

declare(strict_types=1);

namespace Common\Adapter\DI\Exception;

use Common\Domain\Exception\DomainException;

class RouteParametersMissingException extends DomainException
{
    public static function fromMessage(string $message, int $code): static
    {
        return new static($message, $code);
    }
}
