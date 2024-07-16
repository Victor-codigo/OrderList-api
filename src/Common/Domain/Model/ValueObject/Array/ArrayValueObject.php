<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Array;

use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class ArrayValueObject extends ValueObjectBase
{
    protected readonly ?array $value;

    #[\Override]
    public function getValue(): ?array
    {
        return $this->value;
    }

    #[\Override]
    public function getValidationValue(): mixed
    {
        return $this->value;
    }

    public function __construct(?array $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }

    #[\Override]
    public function getValueObjects(): array
    {
        return $this->getValue();
    }

    public function equalTo(ArrayValueObject $value, bool $strict = true): bool
    {
        if ($strict) {
            return $this->value === $value->getValue();
        }

        return $this->value == $value->getValue();
    }
}
