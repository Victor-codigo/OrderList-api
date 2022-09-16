<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type;

use Common\Domain\Exception\LogicException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class TypeBase extends Type
{
    abstract public function getClassImplementationName(): string;

    public function convertToPhpValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        return new ($this->getClassImplementationName())($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof ($this->getClassImplementationName())) {
            throw LogicException::createFromMessage(sprintf('Expected [%s], but got [%s]', $this->getClassImplementationName(), get_class($value)));
        }

        return $value->getValue();
    }

    public function getName(): string
    {
        return end(
            explode('\\', static::class)
        );
    }
}
