<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Array;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\array\Roles;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use JsonException;

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

    /**
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (!$value instanceof Roles) {
            throw InvalidArgumentException::createFromMessage('convertToDatabaseValue - value: is not a '.Roles::class);
        }

        $roles = [];

        foreach (parent::convertToDatabaseValue($value, $platform) as $rol) {
            $roles[] = $rol->getValue()->value;
        }

        return empty($roles) ? null : json_encode($roles, JSON_THROW_ON_ERROR);
    }
}
