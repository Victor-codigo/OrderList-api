<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductModify\Exception;

use Common\Domain\Exception\DomainException;

class ProductModifyProductNameIsAlreadyInDataBaseException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
