<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\TYPES;
use User\Domain\Model\USER_ENTITY_CONSTRAINTS;

class Password extends StringValueObject
{
    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::STRING))
            ->setConstraint(ConstraintFactory::stringRange(USER_ENTITY_CONSTRAINTS::PASSWORD_MIN_LENGTH, USER_ENTITY_CONSTRAINTS::PASSWORD_MAX_LENGTH));
    }
}
