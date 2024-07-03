<?php

declare(strict_types=1);

namespace Common\Domain\HttpClient\Exception;

use DomainException;
class DecodingException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
