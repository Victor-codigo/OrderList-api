<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersRemoveAllGroupsListsOrders\Exception;

use Common\Domain\Exception\DomainException;

class ListOrdersRemoveAllGroupsListsOrdersGroupsAdminsRequestException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
