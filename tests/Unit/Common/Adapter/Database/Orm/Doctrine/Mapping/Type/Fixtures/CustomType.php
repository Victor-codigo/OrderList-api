<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Fixtures;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class CustomType extends TypeBase
{
    public function getClassImplementationName(): string
    {
        return CustomValueObject::class;
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'data base type';
    }
}
