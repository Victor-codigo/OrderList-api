<?php

declare(strict_types=1);

namespace Common\Domain\Database\Orm\Doctrine\Repository\Exception;

use Common\Domain\Exception\DomainInternalErrorException;
use Throwable;

class DBConnectionException extends DomainInternalErrorException implements DBExceptionInterface
{
    public static function fromConnection(int $code, Throwable|null $previous = null): static
    {
        return new static('An error has been occurred when tying to connect to data base ', $code, $previous);
    }
}
