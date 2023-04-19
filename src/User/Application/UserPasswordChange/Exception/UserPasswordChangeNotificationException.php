<?php

declare(strict_types=1);

namespace User\Application\UserPasswordChange\Exception;

use Common\Domain\Exception\DomainException as ExceptionDomainException;

class UserPasswordChangeNotificationException extends ExceptionDomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
