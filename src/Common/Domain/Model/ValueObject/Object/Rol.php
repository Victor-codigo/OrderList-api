<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\TYPES;

class Rol extends ObjectValueObject
{
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::OBJECT))
            ->setConstraint(ConstraintFactory::choice(VALUE_OBJECTS_CONSTRAINTS::ROLES_VALUES, false, null, null, null));
    }
}
