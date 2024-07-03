<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Fixtures;

use Override;
use Common\Domain\Model\ValueObject\Integer\IntegerValueObject;
use Common\Domain\Validation\ConstraintFactory;

class ValueObjectForTesting extends IntegerValueObject
{
    public function __construct(int $age)
    {
        parent::__construct($age);
    }

    #[Override]
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::equalTo(18));
    }
}
