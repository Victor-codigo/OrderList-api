<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderModify\Exception;

use Common\Domain\Exception\DomainException;

class OrderModifyListOrdersIdNotFoundException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
