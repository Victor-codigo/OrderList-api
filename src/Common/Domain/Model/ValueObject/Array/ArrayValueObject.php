<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Array;

use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class ArrayValueObject extends ValueObjectBase
{
    protected readonly array|null $value;

    public function getValue(): array|null
    {
        return $this->value;
    }

    public function __construct(array|null $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }

    public function getValueObjects(): array
    {
        return $this->getValue();
    }
}
