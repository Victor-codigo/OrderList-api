<?php

declare(strict_types=1);

namespace Common\Adapter\Http\ArgumentResolver;

enum REQUEST_ALLOWED_CONTENT: string
{
    case JSON = 'application/json';

    public static function allowed(string|null $type): bool
    {
        return null !== static::tryFrom($type);
    }
}
