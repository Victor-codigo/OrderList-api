<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class StringValueObject extends ValueObjectBase
{
    protected readonly string $value;

    public function getValue(): string
    {
        return $this->value;
    }

    public function __construct(string $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }
}
