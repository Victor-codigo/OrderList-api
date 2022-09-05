<?php

declare(strict_types=1);

namespace Common\Domain\Validation;

enum EMAIL_TYPES: string
{
    case LOOSE = 'loose';
    case HTML5 = 'html5';
    case STRICT = 'strict';
}
