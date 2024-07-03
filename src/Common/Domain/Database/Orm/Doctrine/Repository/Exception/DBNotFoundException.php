<?php

declare(strict_types=1);

namespace Common\Domain\Database\Orm\Doctrine\Repository\Exception;

use Common\Domain\Exception\DomainException;

class DBNotFoundException extends DomainException implements DBExceptionInterface
{
    public static function fromMessage(string $message = '', int $code = 0, ?\Throwable $previous = null): static
    {
        return new static($message, $code, $previous);
    }
}
