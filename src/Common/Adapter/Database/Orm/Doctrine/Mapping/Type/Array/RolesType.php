<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Array;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Model\ValueObject\array\Roles;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class RolesType extends TypeBase
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'JSON';
    }

    public function getClassImplementationName(): string
    {
        return Roles::class;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        return json_encode(
            parent::convertToDatabaseValue($value, $platform),
            JSON_THROW_ON_ERROR
        );
    }
}
