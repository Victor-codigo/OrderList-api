<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductCreate\Exception;

class ProductCreateNameAlreadyExistsException extends \DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
