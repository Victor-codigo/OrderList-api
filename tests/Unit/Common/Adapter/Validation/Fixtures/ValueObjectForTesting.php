<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Fixtures;

use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\ValueObject\Integer\IntegerValueObject;

class ValueObjectForTesting extends IntegerValueObject
{
    public function __construct(int $age)
    {
        parent::__construct($age);
    }

    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::equalTo(18));
    }
}
