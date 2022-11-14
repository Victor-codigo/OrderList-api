<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use User\Domain\Model\USER_ROLES;

interface ValueObjectObjectFactoryInterface
{
    public static function createRol(USER_ROLES|null $rol): Rol;
}