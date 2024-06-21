<?php

declare(strict_types=1);

namespace Order\Application\OrderRemoveAllGroupsOrders\Exception;

use Common\Domain\Exception\DomainException;

class OrderRemoveAllGroupsOrdersGroupsAdminsRequestException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
