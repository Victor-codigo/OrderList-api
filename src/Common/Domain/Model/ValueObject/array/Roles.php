<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\array;

use Common\Domain\Validation\ConstraintFactory;
use User\Domain\Model\USER_ROLES;
use User\Domain\Model\UserEntityConstraints;

class Roles extends ArrayValueObject
{
    public function defineConstraints(): void
    {
        $roles = array_map(
            fn (USER_ROLES $rol) => [$rol],
            UserEntityConstraints::ROLES_VALUES
        );

        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::choice($roles, false, null, null, null));
    }
}
