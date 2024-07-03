<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Float;

use Override;
use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Model\ValueObject\Float\Money;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class MoneyType extends TypeBase
{
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return sprintf('DECIMAL(10,2)');
    }

    #[Override]
    public function getClassImplementationName(): string
    {
        return Money::class;
    }

    #[Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if (null !== $value) {
            $value = (float) $value;
        }

        return parent::convertToPHPValue($value, $platform);
    }
}
