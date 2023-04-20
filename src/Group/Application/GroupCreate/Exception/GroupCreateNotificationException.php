<?php

declare(strict_types=1);

namespace Group\Application\GroupCreate\Exception;

use Common\Domain\Exception\DomainException;

class GroupCreateNotificationException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
