<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Float;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Model\ValueObject\Float\Amount;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class AmountType extends TypeBase
{
    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return sprintf('DECIMAL(10,3)');
    }

    #[\Override]
    public function getClassImplementationName(): string
    {
        return Amount::class;
    }

    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        return parent::convertToPHPValue((float) $value, $platform);
    }
}
