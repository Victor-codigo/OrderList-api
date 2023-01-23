<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\Object\GroupType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Error;
use Group\Domain\Model\GROUP_TYPE;

class GroupTypeType extends TypeBase
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return sprintf('VARCHAR(%d)', $column['length']);
    }

    public function getClassImplementationName(): string
    {
        return GroupType::class;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        $groupType = parent::convertToDatabaseValue($value, $platform);

        return null === $groupType ? null : $groupType->value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        try {
            $groupType = GROUP_TYPE::from($value);

            return parent::convertToPHPValue($groupType, $platform);
        } catch (Error) {
            throw InvalidArgumentException::fromMessage('GroupType: Could not convert from database value, to Php value');
        }
    }
}
