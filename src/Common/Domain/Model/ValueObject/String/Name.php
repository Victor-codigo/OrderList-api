<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\TYPES;
use User\Domain\Model\USER_ENTITY_CONSTRAINTS;

class Name extends StringValueObject
{
    public function __construct(string|null $name)
    {
        parent::__construct($name);
    }

    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::STRING))
            ->setConstraint(ConstraintFactory::stringRange(USER_ENTITY_CONSTRAINTS::NAME_MIN_LENGTH, USER_ENTITY_CONSTRAINTS::NAME_MAX_LENGTH));
    }
}
