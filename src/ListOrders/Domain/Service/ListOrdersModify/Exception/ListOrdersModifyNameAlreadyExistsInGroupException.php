<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersModify\Exception;

use Common\Domain\Exception\DomainException;

class ListOrdersModifyNameAlreadyExistsInGroupException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
