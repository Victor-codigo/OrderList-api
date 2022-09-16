<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject;

use Common\Domain\Validation\CONSTRAINTS_NAMES;
use Common\Domain\Validation\ConstraintDto;
use Common\Domain\Validation\IValueObjectValidation;

abstract class ValueObjectBase implements IValueObjectValidation, ValueObjectInterface
{
    /**
     * @var ConstraintDto[]
     */
    private array $constraints = [];

    abstract protected function defineConstraints(): void;

    protected function setConstraint(ConstraintDto $constraint): static
    {
        $this->constraints[] = $constraint;

        return $this;
    }

    protected function getConstraint(CONSTRAINTS_NAMES $constraint): mixed
    {
        return $this->constraints[$constraint];
    }

    /**
     * @var ConstraintDto[]
     */
    public function getConstraints(): array
    {
        return $this->constraints;
    }
}
