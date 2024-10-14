<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class ObjectValueObject extends ValueObjectBase
{
    protected readonly ?object $value;

    #[\Override]
    public function getValue(): ?object
    {
        return $this->value;
    }

    #[\Override]
    public function getValidationValue(): mixed
    {
        return $this->value;
    }

    public function __construct(?object $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }

    #[\Override]
    public function getValueObjects(): array
    {
        return [];
    }

    public function equalTo(ObjectValueObject $value, bool $strict = true): bool
    {
        if ($strict) {
            return $this->value === $value->getValue();
        }

        return $this->value == $value->getValue();
    }

    #[\Override]
    public function isNull(): bool
    {
        return null === $this->value;
    }
}
