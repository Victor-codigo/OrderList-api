<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Array;

use Override;
use BackedEnum;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\User\USER_ROLES;

class Roles extends ArrayValueObject
{
    #[Override]
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::type(TYPES::ARRAY));
    }

    #[Override]
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
            fn (USER_ROLES $rol): Rol => new Rol($rol),
            $roles
        );

        return new static($rolesCreated);
    }

    /**
     * @return BackedEnum[]
     */
    public function getRolesEnums(): array
    {
        if (null === $this->value) {
            return [];
        }

        return array_map(
            fn (Rol $rol): ?object => $rol->getValue(),
            $this->value
        );
    }
}
