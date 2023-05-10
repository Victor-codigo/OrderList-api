<?php

declare(strict_types=1);

namespace Shop\Domain\Service\ShopCreate\Exception;

class ShopCreateNameAlreadyExistsException extends \DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
