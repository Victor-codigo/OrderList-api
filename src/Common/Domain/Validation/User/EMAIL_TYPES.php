<?php

declare(strict_types=1);

namespace Common\Domain\Validation\User;

enum EMAIL_TYPES: string
{
    case HTML5 = 'html5';
    case STRICT = 'strict';
}
