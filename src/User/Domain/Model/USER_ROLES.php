<?php

declare(strict_types=1);

namespace User\Domain\Model;

enum USER_ROLES: string
{
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';
    case NOT_ACTIVE = 'ROLE_NOT_ACTIVE';
    case USER_FIRST_LOGIN = 'ROLE_USER_FIRST_LOGIN';
    case DELETED = 'ROLE_DELETED';
}
