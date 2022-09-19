<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Fixtures;

use Common\Domain\Model\ValueObject\ValueObjectBase;

class CustomValueObject extends ValueObjectBase
{
    private readonly int|null $value;

    public function __construct(int|null $value)
    {
        $this->value = $value;
    }

    public function getDomainValueObjectClass(): string
    {
        return self::class;
    }

    public function getValue(): int|null
    {
        return $this->value;
    }

    protected function defineConstraints(): void
    {
    }

    public function getValueObjects(): array
    {
        return [];
    }
}
