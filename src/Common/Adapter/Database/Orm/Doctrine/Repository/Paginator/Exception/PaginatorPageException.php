<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Repository\Paginator\Exception;

class PaginatorPageException extends \InvalidArgumentException
{
    public static function formMessage(string $message): static
    {
        return new static($message);
    }
}
