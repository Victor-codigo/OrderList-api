<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\ValueObjectType\Integer;

use Common\Adapter\Database\Orm\Doctrine\Mapping\ValueObjectType\ValueObjectTypeConverterTrait;
use Common\Domain\ValueObject\Integer\Age;
use Doctrine\DBAL\Types\IntegerType;

final class AgeType extends IntegerType
{
    use ValueObjectTypeConverterTrait;

    public function getDomainValueObjectClass(): string
    {
        return Age::class;
    }
}
