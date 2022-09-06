<?php

declare(strict_types=1);

namespace Common\Domain\ValueObject\String;

use Common\Domain\Validation\ConstraintFactory;
use Common\Domain\Validation\EMAIL_TYPES;

class Email extends StringValueObject
{
    public function __construct(string $email)
    {
        parent::__construct($email);
    }

    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::email(EMAIL_TYPES::HTML5));
    }
}
