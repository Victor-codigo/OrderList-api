<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Model\ValueObject\String\IdentifierNullable;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class IdentifierNullableType extends TypeBase
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return sprintf('CHAR(%d)', $column['length']);
    }

    public function getClassImplementationName(): string
    {
        return IdentifierNullable::class;
    }
}
