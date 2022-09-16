<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\array;

class valueObjectArrayFactory
{
    /**
     * @param USER_ROLES[]|null $roles
     */
    public static function createRoles(array|null $roles): Roles
    {
        return new Roles($roles);
    }
}
