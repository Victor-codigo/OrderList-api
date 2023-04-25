<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Float;

use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class FloatValueObject extends ValueObjectBase
{
    protected readonly float|null $value;

    public function __construct(float|null $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }

    public function getValue(): float|null
    {
        return $this->value;
    }

    public function getValidationValue(): mixed
    {
        return $this->value;
    }

    public function getValueObjects(): array
    {
        return [];
    }

    public function equalTo(FloatValueObject $value): bool
    {
        return $this->value === $value->getValue();
    }
}
