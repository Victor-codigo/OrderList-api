<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Validation\ConstraintFactory;
use User\Domain\Model\UserEntityConstraints;

class Password extends StringValueObject
{
    public function __construct(string|null $value)
    {
        parent::__construct($value);
    }

    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::stringRange(UserEntityConstraints::PASSWORD_MIN_LENGTH, UserEntityConstraints::PASSWORD_MAX_LENGTH));
    }
}
