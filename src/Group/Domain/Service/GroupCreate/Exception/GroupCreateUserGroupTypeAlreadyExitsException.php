<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupCreate\Exception;

use Common\Domain\Exception\DomainException;

class GroupCreateUserGroupTypeAlreadyExitsException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
