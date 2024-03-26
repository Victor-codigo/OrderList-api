<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderCreate\Exception;

use Common\Domain\Exception\DomainException;

class OrderCreateProductShopRepeatedException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
