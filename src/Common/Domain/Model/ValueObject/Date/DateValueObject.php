<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Date;

use Common\Domain\Model\ValueObject\ValueObjectBase;

abstract class DateValueObject extends ValueObjectBase
{
    protected readonly ?\DateTime $value;

    public function __construct(?\DateTime $value)
    {
        $this->value = $value;

        $this->defineConstraints();
    }

    #[\Override]
    public function getValue(): ?\DateTime
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

    #[\Override]
    public function isNull(): bool
    {
        return null === $this->value;
    }
}
