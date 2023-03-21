<?php

declare(strict_types=1);

namespace User\Domain\Service\UserCreateGroup\Exception;

use Common\Domain\Exception\DomainException;

class UserCreateGroupUserException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
