<?php

declare(strict_types=1);

namespace Common\Domain\Exception\FileSystem;

use Common\Domain\Exception\DomainException;

class DomainFileSystemException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
