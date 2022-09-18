<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\TYPES;
use User\Domain\Model\PROFILE_ENTITY_CONSTRAINTS;

class Path extends StringValueObject
{
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::STRING))
            ->setConstraint(ConstraintFactory::stringRange(PROFILE_ENTITY_CONSTRAINTS::IMAGE_MIN_LENGTH, PROFILE_ENTITY_CONSTRAINTS::IMAGE_MAX_LENGTH));
    }
}
