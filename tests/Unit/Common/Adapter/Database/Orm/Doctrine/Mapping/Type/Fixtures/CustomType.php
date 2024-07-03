<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Fixtures;

use Override;
use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class CustomType extends TypeBase
{
    #[Override]
    public function getClassImplementationName(): string
    {
        return CustomValueObject::class;
    }

    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'data base type';
    }
}
