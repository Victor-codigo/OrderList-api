<?php

declare(strict_types=1);

namespace Adapter\Framework\Http\Request;

enum REQUEST_ALLOWED_CONTENT: string
{
    case JSON = 'application/json';
}
