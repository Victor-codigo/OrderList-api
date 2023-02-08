<?php

declare(strict_types=1);

namespace Common\Adapter\ModuleComumication\Exception;

use Common\Domain\Exception\DomainException;

class ModuleComunicationException extends DomainException
{
    public static function fromComunicationError(string $message, \Exception $exception): static
    {
        return new static($message, $exception->getCode(), $exception);
    }
}
