<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Fixtures;

use Common\Domain\Model\ValueObject\Array\ArrayValueObject;
use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\TYPES;

class ValueObjectChildValueObjects extends ArrayValueObject
{
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::type(TYPES::ARRAY));
    }
}
