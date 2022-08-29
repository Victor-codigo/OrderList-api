<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\ValueObjectType;

use Common\Domain\Exception\LogicException;
use Doctrine\DBAL\Platforms\AbstractPlatform;

trait ValueObjectTypeConverterTrait
{
    abstract public function getDomainValueObjectClass(): string;

    public function convertToPhpValue($value, AbstractPlatform $platform)
    {
        if (null !== $value) {
            return new ($this->getDomainValueObjectClass())($value);
        }

        return null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof ($this->getDomainValueObjectClass())) {
            throw LogicException::createFromMessage(sprintf('Expected [%s], but got [%s]', $this->getDomainValueObjectClass(), \get_class($value)));
        }

        return $value->getValue();
    }
}
