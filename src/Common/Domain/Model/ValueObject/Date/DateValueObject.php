<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Date;

use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class DateValueObject extends ValueObjectBase
{
    protected readonly \DateTime|null $value;

    public function __construct(\DateTime|null $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }

    #[\Override]
    public function getValue(): \DateTime|null
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

    public function equalTo(DateValueObject $value): bool
    {
        return $this->value === $value->getValue();
    }
}
