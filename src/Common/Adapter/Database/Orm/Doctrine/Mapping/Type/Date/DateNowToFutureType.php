<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Date;

use Common\Domain\Model\ValueObject\Date\DateNowToFuture;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class DateNowToFutureType extends DateType
{
    #[\Override]
    public function getClassImplementationName(): string
    {
        return DateNowToFuture::class;
    }

    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return ValueObjectFactory::createDateNowToFuture(null);
        }

        return parent::convertToPHPValue(new \DateTime($value), $platform);
    }

    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        /** @var \DateTime $dateTime */
        $dateTime = parent::convertToDatabaseValue($value, $platform);

        if (null === $dateTime) {
            return null;
        }

        return $dateTime->format('Y-m-d H:i:s');
    }
}
