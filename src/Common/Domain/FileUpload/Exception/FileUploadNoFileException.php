<?php

declare(strict_types=1);

namespace Common\Domain\FileUpload\Exception;

use Override;
class FileUploadNoFileException extends FileUploadException
{
    #[Override]
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
