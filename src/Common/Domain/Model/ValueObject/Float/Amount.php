<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Float;

use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class Amount extends FloatValueObject
{
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::FLOAT))
            ->setConstraint(ConstraintFactory::positiveOrZero());
    }
}
