<?php

declare(strict_types=1);

namespace Common\Adapter\Command\HexFilesStructure\Exception;

use Common\Domain\Exception\DomainException;

class TemplateErrorException extends DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
