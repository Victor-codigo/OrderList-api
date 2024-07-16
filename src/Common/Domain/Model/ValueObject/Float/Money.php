<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Float;

use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class Money extends FloatValueObject
{
    #[\Override]
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::type(TYPES::FLOAT))
            ->setConstraint(ConstraintFactory::positiveOrZero());
    }
}
