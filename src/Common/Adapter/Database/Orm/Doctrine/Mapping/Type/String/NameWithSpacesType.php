<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String;

use Override;
use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class NameWithSpacesType extends TypeBase
{
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return sprintf('VARCHAR(%d)', $column['length']);
    }

    #[Override]
    public function getClassImplementationName(): string
    {
        return NameWithSpaces::class;
    }
}
