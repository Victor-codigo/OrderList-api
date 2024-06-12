<?php

declare(strict_types=1);

namespace Group\Application\GroupRemoveAllUserGroups\Exception;

use Common\Domain\Exception\DomainException;

class GroupRemoveAllUserGroupsNotificationException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
