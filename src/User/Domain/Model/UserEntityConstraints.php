<?php

declare(strict_types=1);

namespace User\Domain\Model;

final class UserEntityConstraints
{
    public const ID_LENGTH = 36;
    public const ID_NOT_NULL = true;
    public const ID_UNIQUE = true;
    public const ID_TYPE = 'string';

    public const EMAIL_LENGTH = 50;
    public const EMAIL_NOT_NULL = true;
    public const EMAIL_UNIQUE = true;
    public const EMAIL_TYPE = 'string';

    public const PASSWORD_MIN_LENGTH = 6;
    public const PASSWORD_MAX_LENGTH = 256;
    public const PASSWORD_NOT_NULL = true;
    public const PASSWORD_UNIQUE = true;
    public const PASSWORD_TYPE = 'string';

    public const NAME_MIN_LENGTH = 4;
    public const NAME_MAX_LENGTH = 50;
    public const NAME_NOT_NULL = true;
    public const NAME_UNIQUE = true;
    public const NAME_TYPE = 'string';

    public const ROLES_LENGTH = 50;
    public const ROLES_NOT_NULL = true;
    public const ROLES_UNIQUE = true;
    public const ROLES_TYPE = 'json';
    public const ROLES_VALUES = [USER_ROLES::ADMIN, USER_ROLES::USER];

    public const CREATED_ON_LENGTH = 50;
    public const CREATED_ON_NOT_NULL = true;
    public const CREATED_ON_UNIQUE = true;
    public const CREATED_ON_TYPE = 'json';
}
