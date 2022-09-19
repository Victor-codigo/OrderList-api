<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\TYPES;
use User\Domain\Model\USER_ENTITY_CONSTRAINTS;

class Rol extends ObjectValueObject
{
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::OBJECT))
            ->setConstraint(ConstraintFactory::choice(USER_ENTITY_CONSTRAINTS::ROLES_VALUES, false, null, null, null));
    }
}
