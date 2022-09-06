<?php

declare(strict_types=1);

namespace Common\Domain\ValueObject\String;

use Common\Domain\Validation\ConstraintFactory;

class Name extends StringValueObject
{
    private const NAME_MIN_LENGTH = 4;
    private const NAME_MAX_LENGTH = 50;

    public function __construct(string $name)
    {
        parent::__construct($name);
    }

    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::stringRange(self::NAME_MIN_LENGTH, self::NAME_MAX_LENGTH));
    }
}
