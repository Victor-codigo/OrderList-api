<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersCreate\Exception;

use Common\Domain\Exception\DomainException;

class ListOrdersCreateNameAlreadyExistsInGroupException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
