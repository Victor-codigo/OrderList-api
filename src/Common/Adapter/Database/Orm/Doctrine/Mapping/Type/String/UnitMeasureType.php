<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\Object\UnitMeasure;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class UnitMeasureType extends TypeBase
{
    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return sprintf('CHAR(%d)', $column['length']);
    }

    #[\Override]
    public function getClassImplementationName(): string
    {
        return UnitMeasure::class;
    }

    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        /** @var UNIT_MEASURE_TYPE $notificationType */
        $unitType = parent::convertToDatabaseValue($value, $platform);

        return null === $unitType ? null : $unitType->value;
    }

    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        try {
            $unitType = UNIT_MEASURE_TYPE::from($value);

            return parent::convertToPHPValue($unitType, $platform);
        } catch (\Error) {
            throw InvalidArgumentException::fromMessage('UnitMeasureType: Could not convert from database value, to Php value');
        }
    }
}
