<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class NameWithSpaces extends StringValueObject
{
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::STRING))
            ->setConstraint(ConstraintFactory::alphanumericWithWhiteSpace())
            ->setConstraint(ConstraintFactory::stringRange(VALUE_OBJECTS_CONSTRAINTS::NAME_MIN_LENGTH, VALUE_OBJECTS_CONSTRAINTS::NAME_MAX_LENGTH));
    }

    public function __toString()
    {
        return $this->getValue();
    }
}
