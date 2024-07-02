<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\Array;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\Array\NotificationData;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class NotificationDataType extends TypeBase
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'JSON';
    }

    public function getClassImplementationName(): string
    {
        return NotificationData::class;
    }

    /**
     * @return string|null
     *
     * @throws \JsonException
     * @throws InvalidArgumentException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (!$value instanceof NotificationData) {
            throw InvalidArgumentException::fromMessage('convertToDatabaseValue - value: is not a '.NotificationData::class);
        }

        $valueToSave = parent::convertToDatabaseValue($value, $platform);

        return empty($valueToSave) ? null : json_encode($valueToSave, JSON_THROW_ON_ERROR);
    }

    /**
     * @return NotificationData|null
     *
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return new NotificationData([]);
        }

        try {
            $notificationData = json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR);

            return parent::convertToPHPValue($notificationData, $platform);
        } catch (\JsonException) {
            throw InvalidArgumentException::fromMessage('convertToPHPValue: data base notification data, is not a valid json string');
        } catch (\Throwable) {
            throw LogicException::fromMessage('convertToPHPValue: data base notification data, can\'t be converted to a '.NotificationData::class);
        }
    }
}
