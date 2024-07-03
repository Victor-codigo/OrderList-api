<?php

declare(strict_types=1);

namespace Common\Domain\FileUpload\Exception\File;

class FileException extends \DomainException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
