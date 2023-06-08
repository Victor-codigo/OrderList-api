<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersAddOrder\Exception;

use Common\Domain\Exception\DomainException;

class ListOrdersAddOrdersOrdersNotFoundException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static ($message);
    }
}
