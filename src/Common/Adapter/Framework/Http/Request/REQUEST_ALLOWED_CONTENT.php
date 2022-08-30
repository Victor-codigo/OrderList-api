<?php

declare(strict_types=1);

namespace Common\Adapter\Framework\Http\Request;

enum REQUEST_ALLOWED_CONTENT: string
{
    case JSON = 'application/json';

    public static function has($type)
    {
        return in_array($type, self::cases(), true);
    }
}
