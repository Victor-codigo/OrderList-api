<?php

declare(strict_types=1);

namespace Group\Application\GroupUserAdd\Exception;

use Common\Domain\Exception\DomainException;

class GroupUserAddNotificationException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
