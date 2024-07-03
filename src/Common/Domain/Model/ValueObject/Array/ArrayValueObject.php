<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Array;

use Override;
use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class ArrayValueObject extends ValueObjectBase
{
    protected readonly array|null $value;

    #[Override]
    public function getValue(): array|null
    {
        return $this->value;
    }

    #[Override]
    public function getValidationValue(): mixed
    {
        return $this->value;
    }

    public function __construct(array|null $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }

    #[Override]
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
