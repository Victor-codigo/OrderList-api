<?php

declare(strict_types=1);

namespace Common\Domain\Image\Exception;

use Common\Domain\Exception\DomainException;

class ImageResizeException extends DomainException
{
    public static function fromMessage(string $message)
    {
        return new self($message);
    }
}
