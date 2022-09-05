<?php

declare(strict_types=1);

namespace Common\Domain\ValueObject;

use Common\Domain\Validation\CONSTRAINTS_NAMES;
use Common\Domain\Validation\ConstraintDto;
use Common\Domain\Validation\IValueObjectValidation;

abstract class ValueObjectBase implements IValueObjectValidation
{
    /**
     * @var ConstraintDto[]
     */
    private array $constraint = [];

    abstract protected function defineConstraints(): void;

    protected function setConstraint(ConstraintDto $constraint): static
    {
        $this->constraint[] = $constraint;

        return $this;
    }

    protected function getConstraint(CONSTRAINTS_NAMES $constraint): mixed
    {
        return $this->constraint[$constraint];
    }

    /**
     * @var ConstraintDto[]
     */
    public function getConstraints(): array
    {
        return $this->constraint;
    }
}
