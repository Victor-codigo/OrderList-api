<?php

declare(strict_types=1);

namespace Product\Domain\Service\ValidateGroupAndUser\Exception;

use Common\Domain\Exception\DomainException;

class ValidateGroupAndUserException extends DomainException
{
    public static function fromMessage(string $message)
    {
        return new static($message);
    }
}
