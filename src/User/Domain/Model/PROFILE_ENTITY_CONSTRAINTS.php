<?php

declare(strict_types=1);

namespace User\Domain\Model;

class PROFILE_ENTITY_CONSTRAINTS
{
    public const IMAGE_MIN_LENGTH = 1;
    public const IMAGE_MAX_LENGTH = 256;
    public const IMAGE_NOT_NULL = false;
    public const IMAGE_UNIQUE = true;
    public const IMAGE_TYPE = 'string';
}
