<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class Password extends StringValueObject
{
    #[\Override]
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::STRING))
            ->setConstraint(ConstraintFactory::stringRange(VALUE_OBJECTS_CONSTRAINTS::PASSWORD_MIN_LENGTH, VALUE_OBJECTS_CONSTRAINTS::PASSWORD_MAX_LENGTH));
    }
}
