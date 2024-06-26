<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserRemove\Exception;

use Common\Domain\Exception\DomainException;

class GroupUserRemoveGroupWithoutAdminException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
