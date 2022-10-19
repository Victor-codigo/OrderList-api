<?php

declare(strict_types=1);

namespace Common\Domain\Database\Orm\Doctrine\Repository\Exception;

use Throwable;

class DBNotFoundException implements DBExceptionInterface
{
    public static function fromMessage(string $message = '', int $code = 0, Throwable|null $previous = null): static
    {
        return new static($message, $code, $previous);
    }
}
