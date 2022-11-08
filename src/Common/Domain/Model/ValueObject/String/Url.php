<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\PROTOCOLS;
use Common\Domain\Validation\TYPES;

class Url extends StringValueObject
{
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::STRING))
            ->setConstraint(ConstraintFactory::url([PROTOCOLS::HTTP, PROTOCOLS::HTTPS]));
    }
}
