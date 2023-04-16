<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserAdd\Exception;

class GroupAddUsersAlreadyInTheGroupException extends \DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
