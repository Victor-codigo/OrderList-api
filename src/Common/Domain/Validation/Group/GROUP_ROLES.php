<?php

declare(strict_types=1);

namespace Common\Domain\Validation\Group;

enum GROUP_ROLES: string
{
    case ADMIN = 'GROUP_ROLE_ADMIN';
    case USER = 'GROUP_ROLE_USER';
}
