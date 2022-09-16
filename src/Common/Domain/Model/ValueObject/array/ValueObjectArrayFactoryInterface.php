<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\array;

interface ValueObjectArrayFactoryInterface
{
    /**
     * @param USER_ROLES[]|null $roles
     */
    public static function createRoles(array|null $roles): Roles;
}
