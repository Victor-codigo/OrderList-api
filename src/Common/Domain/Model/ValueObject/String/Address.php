<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class Address extends StringValueObject implements \Stringable
{
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::type(TYPES::STRING))
            ->setConstraint(ConstraintFactory::stringRange(VALUE_OBJECTS_CONSTRAINTS::ADDRESS_MIN_LENGTH, VALUE_OBJECTS_CONSTRAINTS::ADDRESS_MAX_LENGTH))
            ->setConstraint(ConstraintFactory::regEx('/^[a-zÀ-ÿ0-9\s,_\-\\\\\.\#]+$/ui', true));
    }

    public function __toString(): string
    {
        return (string) $this->getValue();
    }
}
