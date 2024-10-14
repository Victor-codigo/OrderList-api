<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class StringValueObject extends ValueObjectBase
{
    protected readonly ?string $value;

    #[\Override]
    public function getValue(): ?string
    {
        return $this->value;
    }

    #[\Override]
    public function getValidationValue(): mixed
    {
        return $this->value;
    }

    public function __construct(?string $value)
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

    #[\Override]
    public function isNull(): bool
    {
        return null === $this->value;
    }
}
