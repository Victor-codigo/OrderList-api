<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Date;

use Override;
use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class DateType extends TypeBase
{
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return sprintf('DATETIME');
    }
}
