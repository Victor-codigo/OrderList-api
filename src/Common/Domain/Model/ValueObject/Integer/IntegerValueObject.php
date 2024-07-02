<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Integer;

use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class IntegerValueObject extends ValueObjectBase
{
    protected readonly int|null $value;

    public function __construct(int|null $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }

    #[\Override]
    public function getValue(): int|null
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

    public function equalTo(IntegerValueObject $value): bool
    {
        return $this->value === $value->getValue();
    }
}
