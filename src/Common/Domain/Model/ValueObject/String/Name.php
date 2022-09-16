<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Validation\ConstraintFactory;
use User\Domain\Model\UserEntityConstraints;

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
            ->setConstraint(ConstraintFactory::stringRange(UserEntityConstraints::NAME_MIN_LENGTH, UserEntityConstraints::NAME_MAX_LENGTH));
    }
}
