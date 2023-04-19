<?php

declare(strict_types=1);

namespace User\Application\UserPasswordRememberChange\Exception;

use Common\Domain\Exception\DomainException;

class UserPasswordRememberChangeNotificationException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static ($message);
    }
}
