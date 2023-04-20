<?php

declare(strict_types=1);

namespace Group\Application\GroupRemove\Exception;

use Common\Domain\Exception\DomainException;

class GroupRemoveGroupNotificationException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
