<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class StringValueObject extends ValueObjectBase
{
    protected readonly string|null $value;

    #[\Override]
    public function getValue(): string|null
    {
        return $this->value;
    }

    #[\Override]
    public function getValidationValue(): mixed
    {
        return $this->value;
    }

    public function __construct(string|null $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }

    #[\Override]
    public function getValueObjects(): array
    {
        return [];
    }

    public function equalTo(StringValueObject $value): bool
    {
        return $this->value === $value->getValue();
    }
}
