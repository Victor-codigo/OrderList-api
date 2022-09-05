<?php

declare(strict_types=1);

namespace Common\Domain\ValueObject\String;

use Common\Domain\ValueObject\ValueObjectBase;

abstract class StringValueObject extends ValueObjectBase
{
    protected readonly string $value;

    public function __construct(string $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }
}
