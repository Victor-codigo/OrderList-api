<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Fixtures;

use Override;
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

    #[Override]
    public function getValue(): int|null
    {
        return $this->value;
    }

    #[Override]
    public function getValidationValue(): mixed
    {
        return $this->value;
    }

    #[Override]
    protected function defineConstraints(): void
    {
    }

    #[Override]
    public function getValueObjects(): array
    {
        return [];
    }
}
