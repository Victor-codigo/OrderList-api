<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type;

use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\ValueObjectBase;
use Doctrine\DBAL\Platforms\AbstractPlatform;

trait ValueObjectTypeConverterTrait
{
    public function convertToPhpValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        return new ($this->getDomainValueObjectClass())($value);
    }

    public function convertToDatabaseValue(ValueObjectBase $value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof ($this->getDomainValueObjectClass())) {
            throw LogicException::createFromMessage(sprintf('Expected [%s], but got [%s]', $this->getDomainValueObjectClass(), get_class($value)));
        }

        return $value->getValue();
    }

    public function getDomainValueObjectClass(): string
    {
        return self::class;
    }
}
