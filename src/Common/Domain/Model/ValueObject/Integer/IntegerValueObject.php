<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Integer;

use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class IntegerValueObject extends ValueObjectBase
{
    protected readonly ?int $value;

    public function __construct(?int $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }

    #[\Override]
    public function getValue(): ?int
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

    #[\Override]
    public function isNull(): bool
    {
        return null === $this->value;
    }
}
