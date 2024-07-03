<?php

declare(strict_types=1);

namespace Common\Adapter\ModuleCommunication\Exception;

use Exception;
use Common\Domain\Exception\DomainException;

class ModuleCommunicationException extends DomainException
{
    public static function fromCommunicationError(string $message, Exception $exception): static
    {
        return new static($message, $exception->getCode(), $exception);
    }
}
