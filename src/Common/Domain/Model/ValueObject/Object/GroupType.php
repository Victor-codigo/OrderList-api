<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class GroupType extends ObjectValueObject
{
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::OBJECT))
            ->setConstraint(ConstraintFactory::choice(VALUE_OBJECTS_CONSTRAINTS::GROUP_TYPE_VALUES, false, null, null, null));
    }
}
