<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserRoleChange\Exception;

use Common\Domain\Exception\DomainException;

class GroupWithoutAdminsException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
