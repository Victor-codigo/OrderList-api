<?php

declare(strict_types=1);

namespace User\Domain\Model;

enum USER_ROLES: string
{
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';
}
