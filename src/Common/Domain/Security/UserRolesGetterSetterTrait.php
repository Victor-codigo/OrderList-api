<?php

declare(strict_types=1);

namespace Common\Domain\Security;

use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\User\USER_ROLES;

trait UserRolesGetterSetterTrait
{
    public function getRoles(): Roles
    {
        $roles = $this->roles->getValue();
        $rolSearched = new Rol(USER_ROLES::USER);
        $rolNotActive = new Rol(USER_ROLES::NOT_ACTIVE);
        $rolDeleted = new Rol(USER_ROLES::DELETED);

        if (!$this->roles->has($rolSearched) && !$this->roles->has($rolNotActive) && !$this->roles->has($rolDeleted)) {
            $roles[] = $rolSearched;
        }

        return ValueObjectFactory::createRoles($roles);
    }

    public function setRoles(Roles $roles): self
    {
        $this->roles = $roles;

        return $this;
    }
}
