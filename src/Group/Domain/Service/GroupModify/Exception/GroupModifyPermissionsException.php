<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupModify\Exception;

use Common\Domain\Exception\DomainException;

class GroupModifyPermissionsException extends DomainException
{
    public static function fromMessage(string $message): self
    {
        return new self($message);
    }
}
