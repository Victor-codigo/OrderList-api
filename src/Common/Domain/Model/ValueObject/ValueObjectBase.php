<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject;

use Common\Domain\Validation\ConstraintDto;
use Common\Domain\Validation\ValueObjectValidationInterface;

abstract class ValueObjectBase implements ValueObjectValidationInterface, ValueObjectInterface
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

    /**
     * @return ConstraintDto[]
     */
    #[\Override]
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    /**
     * @return array{}
     */
    #[\Override]
    public function getValueObjects(): array
    {
        return [];
    }
}
