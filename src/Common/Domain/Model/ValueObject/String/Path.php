<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Validation\ConstraintFactory;

class Path extends StringValueObject
{
    public const PATH_MIN_LENGTH = 1;
    public const PATH_MAX_LENGTH = 256;

    public function __construct(string $path)
    {
        parent::__construct($path);
    }

    public function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::stringRange(self::PATH_MIN_LENGTH, self::PATH_MAX_LENGTH));
    }
}
