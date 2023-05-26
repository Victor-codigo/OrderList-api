<?php

declare(strict_types=1);

namespace Common\Domain\Database\Orm\Doctrine\Repository\Exception;

use Common\Domain\Exception\DomainException;

class DBUniqueConstraintException extends DomainException implements DBExceptionInterface
{
    public static function fromEmail(string $email, int $code = 0): static
    {
        return new static(sprintf('The email [%s], already exists', $email), $code);
    }

    public static function fromId(string|int $id, int $code = 0): static
    {
        return new static(sprintf('The id [%s], already exists', $id), $code);
    }
}
