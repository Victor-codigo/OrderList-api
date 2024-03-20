<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderCreate\Exception;

use Common\Domain\Exception\DomainException;

class OrderCreateShopNotFoundException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
