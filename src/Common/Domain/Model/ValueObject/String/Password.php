<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Validation\ConstraintFactory;

class Password extends StringValueObject
{
    public const PASSWORD_MIN_CHARS = 6;
    public const PASSWORD_MAX_CHARS = 256;

    public function __construct(string $value)
    {
        parent::__construct($value);
    }

    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::stringRange(self::PASSWORD_MIN_CHARS, self::PASSWORD_MAX_CHARS));
    }
}
