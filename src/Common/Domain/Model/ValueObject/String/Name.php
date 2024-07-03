<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Stringable;
use Override;
use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class Name extends StringValueObject implements Stringable
{
    #[Override]
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::STRING))
            ->setConstraint(ConstraintFactory::alphanumeric())
            ->setConstraint(ConstraintFactory::stringRange(VALUE_OBJECTS_CONSTRAINTS::NAME_MIN_LENGTH, VALUE_OBJECTS_CONSTRAINTS::NAME_MAX_LENGTH));
    }

    #[Override]
    public function __toString(): string
    {
        return (string) $this->getValue();
    }
}
