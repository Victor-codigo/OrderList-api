<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\array;

use Common\Domain\Model\ValueObject\Object\Rol;

class valueObjectArrayFactory
{
    /**
     * @param Rol[]|null $roles
     */
    public static function createRoles(array|null $roles): Roles
    {
        return new Roles($roles);
    }
}
