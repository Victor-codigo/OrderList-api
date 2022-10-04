<?php

declare(strict_types=1);

namespace Common\Adapter\DI;

class RouteInvalidParameterException
{
    public static function create(string $message, int $code): self
    {
        return new self($message, $code);
    }
}
