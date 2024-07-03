<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Override;
use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class Path extends StringValueObject
{
    #[Override]
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::STRING))
            ->setConstraint(ConstraintFactory::stringRange(VALUE_OBJECTS_CONSTRAINTS::PATH_MIN_LENGTH, VALUE_OBJECTS_CONSTRAINTS::PATH_MAX_LENGTH));
    }
}
