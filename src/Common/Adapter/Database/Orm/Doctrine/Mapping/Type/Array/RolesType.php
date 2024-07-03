<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Array;

use Override;
use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\Object\Rol;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Exception;
use JsonException;

class RolesType extends TypeBase
{
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'JSON';
    }

    #[Override]
    public function getClassImplementationName(): string
    {
        return Roles::class;
    }

    /**
     * @return string|null
     *
     * @throws JsonException
     * @throws InvalidArgumentException
     */
    #[Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (!$value instanceof Roles) {
            throw InvalidArgumentException::fromMessage('convertToDatabaseValue - value: is not a '.Roles::class);
        }

        $roles = [];

        foreach (parent::convertToDatabaseValue($value, $platform) as $rol) {
            $roles[] = $rol->getValue()->value;
        }

        return empty($roles) ? null : json_encode($roles, JSON_THROW_ON_ERROR);
    }

    /**
     * @return Roles|null
     *
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    #[Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        try {
            $roles = json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR);
            $rolesObject = [];

            foreach ($roles as $rol) {
                $rolesObject[] = Rol::fromString($rol);
            }

            return new Roles($rolesObject);
        } catch (JsonException) {
            throw InvalidArgumentException::fromMessage('convertToPHPValue: data base roles, is not a valid json string');
        } catch (Exception) {
            throw LogicException::fromMessage('convertToPHPValue: data base roles, can\'t be converted to a rol');
        }
    }
}
