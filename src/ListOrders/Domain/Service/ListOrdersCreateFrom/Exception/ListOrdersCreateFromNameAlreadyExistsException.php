<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersCreateFrom\Exception;

use Common\Domain\Exception\DomainException;

class ListOrdersCreateFromNameAlreadyExistsException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
