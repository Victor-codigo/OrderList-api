<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Repository\Paginator\Exception;

use InvalidArgumentException;
class PaginatorPageException extends InvalidArgumentException
{
    public static function fromMessage(string $message): static
    {
        return new static($message);
    }
}
