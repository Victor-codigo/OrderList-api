<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopModify\Exception;

use Common\Domain\Exception\DomainException;

class ShopModifyNameIsAlreadyInDataBaseException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
