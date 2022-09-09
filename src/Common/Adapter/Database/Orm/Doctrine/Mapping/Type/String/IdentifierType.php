<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\ValueObjectType\String;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\ValueObjectTypeConverterTrait;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class IdentifierType extends StringType
{
    use ValueObjectTypeConverterTrait;

    // public const NAME = 'Identifier';

    // public function getName()
    // {
    //     return self::NAME;
    // }

    // public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    // {
    //     return 'CHAR(36) NOT NULL';
    // }

    // public function canRequireSQLConversion()
    // {
    //     return true;
    // }
}
