<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupRemove\Exception;

use Common\Domain\Exception\DomainException;

class GroupRemovePermissionsException extends DomainException
{
    public static function fromMessage(string $message): self
    {
        return new self($message);
    }
}
