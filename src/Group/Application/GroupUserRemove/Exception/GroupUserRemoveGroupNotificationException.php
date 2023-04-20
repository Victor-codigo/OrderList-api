<?php

declare(strict_types=1);

namespace Group\Application\GroupUserRemove\Exception;

use Common\Domain\Exception\DomainException;

class GroupUserRemoveGroupNotificationException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
