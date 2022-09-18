<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\array;

use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\TYPES;
use User\Domain\Model\USER_ENTITY_CONSTRAINTS;
use User\Domain\Model\USER_ROLES;

class Roles extends ArrayValueObject
{
    public function defineConstraints(): void
    {
        $roles = array_map(
            fn (USER_ROLES $rol) => [$rol],
            USER_ENTITY_CONSTRAINTS::ROLES_VALUES
        );

        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::ARRAY))
            ->setConstraint(ConstraintFactory::choice($roles, false, null, null, null));
    }
}
