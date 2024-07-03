<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type;

use Common\Domain\Exception\InvalidArgumentException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class TypeBase extends Type
{
    abstract public function getClassImplementationName(): string;

    #[\Override]
    public function getName(): string
    {
        return static::class;
    }

    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof ($this->getClassImplementationName())) {
            throw InvalidArgumentException::fromMessage('convertToDatabaseValue: Passed data type is wrong');
        }

        return $value->getValue();
    }

    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        return new ($this->getClassImplementationName())($value);
    }
}
