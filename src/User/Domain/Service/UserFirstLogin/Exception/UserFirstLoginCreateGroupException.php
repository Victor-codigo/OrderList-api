<?php

declare(strict_types=1);

namespace User\Domain\Service\UserFirstLogin\Exception;

use Common\Domain\Exception\DomainException;

class UserFirstLoginCreateGroupException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static ($message);
    }
}
