<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class ObjectValueObject extends ValueObjectBase
{
    protected readonly object|null $value;

    public function getValue(): object|null
    {
        return $this->value;
    }

    public function __construct(object|null $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }

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
}
