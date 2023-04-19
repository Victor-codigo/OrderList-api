<?php

declare(strict_types=1);

namespace User\Application\UserEmailChange\Exception;

class UserEmailChangeCreateNotificationException extends \DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static ($message);
    }
}
