<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\TYPES;
use Common\Domain\Validation\User\EMAIL_TYPES;

class Email extends StringValueObject
{
    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull())
            ->setConstraint(ConstraintFactory::type(TYPES::STRING))
            ->setConstraint(ConstraintFactory::email(EMAIL_TYPES::HTML5));
    }
}
