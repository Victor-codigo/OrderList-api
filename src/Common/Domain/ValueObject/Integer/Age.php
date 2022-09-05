<?php

declare(strict_types=1);

namespace Common\Domain\ValueObject\Integer;

use Common\Domain\Validation\ConstraintFactory;

class Age extends IntegerValueObject
{
    public function __construct(int $value)
    {
        parent::__construct($value);

        $this->defineConstraints();
    }

    protected function defineConstraints(): void
    {
        $this
            ->setConstraint(ConstraintFactory::notBlank())
            ->setConstraint(ConstraintFactory::notNull());
    }
}
