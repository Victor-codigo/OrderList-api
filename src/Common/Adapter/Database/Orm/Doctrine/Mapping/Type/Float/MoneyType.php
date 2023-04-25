<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Float;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Model\ValueObject\Float\Money;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class MoneyType extends TypeBase
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return sprintf('DECIMAL(10,2)');
    }

    public function getClassImplementationName(): string
    {
        return Money::class;
    }
}
