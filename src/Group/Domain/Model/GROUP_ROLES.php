<?php

declare(strict_types=1);

namespace Group\Domain\Model;

enum GROUP_ROLES: string
{
    case ADMIN = 'GROUP_ROLE_ADMIN';
    case USER = 'GROUP_ROLE_USER';
}
