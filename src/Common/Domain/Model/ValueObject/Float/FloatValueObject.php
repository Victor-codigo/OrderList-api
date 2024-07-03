<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Float;

use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class FloatValueObject extends ValueObjectBase
{
    protected readonly ?float $value;

    public function __construct(?float $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }

    #[\Override]
    public function getValue(): ?float
    {
        return $this->value;
    }

    #[\Override]
    public function getValidationValue(): mixed
    {
        return $this->value;
    }

    #[\Override]
    public function getValueObjects(): array
    {
        return [];
    }

    public function equalTo(FloatValueObject $value): bool
    {
        return $this->value === $value->getValue();
    }
}
