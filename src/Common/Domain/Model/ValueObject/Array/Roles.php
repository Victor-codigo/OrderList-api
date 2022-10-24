<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Array;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\TYPES;
use User\Domain\Model\USER_ROLES;

class Roles extends ArrayValueObject
{
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::type(TYPES::ARRAY));
    }

    public function getValueObjects(): array
    {
        if (null === $this->getValue()) {
            return [];
        }

        return $this->getValue();
    }

    public function has(Rol $rol): bool
    {
        return in_array($rol, $this->getValue());
    }

    /**
     * @param USER_ROLES[]
     */
    public static function create(array $roles): static
    {
        $rolesCreated = array_map(
            fn (USER_ROLES $rol) => new Rol($rol),
            $roles
        );

        return new static($rolesCreated);
    }
}
