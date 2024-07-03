<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Override;
use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;
use Common\Domain\Validation\ConstraintFactory;

class JwtToken extends StringValueObject
{
    #[Override]
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::stringMin(VALUE_OBJECTS_CONSTRAINTS::JWT_TOKEN_MIN_LENGTH));
    }
}
