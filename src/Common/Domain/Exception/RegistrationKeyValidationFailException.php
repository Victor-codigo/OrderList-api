<?php

declare(strict_types=1);

namespace Common\Domain\Exception;

use DomainException;

class RegistrationKeyValidationFailException extends DomainException
{
    private function __construct(string $message)
    {
        $this->message = $message;
    }

    public static function create(string $message): self
    {
        return new self($message);
    }
}
