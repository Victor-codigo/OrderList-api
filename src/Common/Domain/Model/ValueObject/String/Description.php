<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\TYPES;

class Description extends StringValueObject
{
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::type(TYPES::STRING))
            ->setConstraint(ConstraintFactory::stringMax(VALUE_OBJECTS_CONSTRAINTS::DESCRIPTION_MAX_LENGTH));
    }
}
