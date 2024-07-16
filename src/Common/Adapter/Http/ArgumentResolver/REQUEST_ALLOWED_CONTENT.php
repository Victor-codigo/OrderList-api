<?php

declare(strict_types=1);

namespace Common\Adapter\Http\ArgumentResolver;

enum REQUEST_ALLOWED_CONTENT: string
{
    case JSON = 'application/json';
    case FORM_DATA = 'multipart/form-data';

    public static function allowed(?string $type): bool
    {
        if (null === $type) {
            return false;
        }

        return null !== static::tryFrom($type);
    }
}
