<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Constraints;

use User\Domain\Model\USER_ROLES;

final class VALUE_OBJECTS_CONSTRAINTS
{
    public const ID_LENGTH = 36;
    public const ID_TYPE = 'string';

    public const EMAIL_LENGTH = 50;
    public const EMAIL_TYPE = 'string';

    public const PASSWORD_MIN_LENGTH = 6;
    public const PASSWORD_MAX_LENGTH = 50;
    public const PASSWORD_TYPE = 'string';

    public const NAME_MIN_LENGTH = 1;
    public const NAME_MAX_LENGTH = 50;
    public const NAME_TYPE = 'string';

    public const ROLES_LENGTH = 50;
    public const ROLES_TYPE = 'json';
    public const ROLES_VALUES = [USER_ROLES::ADMIN, USER_ROLES::USER, USER_ROLES::NOT_ACTIVE];

    public const CREATED_ON_LENGTH = 50;
    public const CREATED_ON_TYPE = 'json';

    public const IMAGE_MIN_LENGTH = 1;
    public const IMAGE_MAX_LENGTH = 256;
    public const IMAGE_TYPE = 'string';

    public const JWT_TOKEN_MIN_LENGTH = 36;
}
