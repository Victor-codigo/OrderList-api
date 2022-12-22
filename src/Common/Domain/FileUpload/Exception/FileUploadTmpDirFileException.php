<?php

declare(strict_types=1);

namespace Common\Domain\FileUpload\Exception;

class FileUploadTmpDirFileException extends FileUploadException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
