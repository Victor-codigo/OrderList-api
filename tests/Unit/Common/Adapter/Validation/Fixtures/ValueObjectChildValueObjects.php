<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Validation\Fixtures;

use Override;
use Common\Domain\Model\ValueObject\Array\ArrayValueObject;
use Common\Domain\Validation\Common\TYPES;
use Common\Domain\Validation\ConstraintFactory;

class ValueObjectChildValueObjects extends ArrayValueObject
{
    #[Override]
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::type(TYPES::ARRAY));
    }
}
