<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Float;

use Override;
use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class FloatValueObject extends ValueObjectBase
{
    protected readonly float|null $value;

    public function __construct(float|null $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }

    #[Override]
    public function getValue(): float|null
    {
        return $this->value;
    }

    #[Override]
    public function getValidationValue(): mixed
    {
        return $this->value;
    }

    #[Override]
    public function getValueObjects(): array
    {
        return [];
    }

    public function equalTo(FloatValueObject $value): bool
    {
        return $this->value === $value->getValue();
    }
}
