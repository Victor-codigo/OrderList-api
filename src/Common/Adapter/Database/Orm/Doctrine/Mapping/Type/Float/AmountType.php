<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Float;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Model\ValueObject\Float\Amount;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class AmountType extends TypeBase
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return sprintf('DECIMAL(10,3)');
    }

    public function getClassImplementationName(): string
    {
        return Amount::class;
    }
}
