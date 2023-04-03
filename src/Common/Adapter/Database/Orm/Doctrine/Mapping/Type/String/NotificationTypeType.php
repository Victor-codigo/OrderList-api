<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\Object\NotificationType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Error;
use Notification\Domain\Model\NOTIFICATION_TYPE;

class NotificationTypeType extends TypeBase
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return sprintf('VARCHAR(%d)', $column['length']);
    }

    public function getClassImplementationName(): string
    {
        return NotificationType::class;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        /** @var NOTIFICATION_TYPE $notificationType */
        $notificationType = parent::convertToDatabaseValue($value, $platform);

        return null === $notificationType ? null : $notificationType->value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        try {
            $notificationType = NOTIFICATION_TYPE::from($value);

            return parent::convertToPHPValue($notificationType, $platform);
        } catch (Error) {
            throw InvalidArgumentException::fromMessage('NotificationTypeTpe: Could not convert from database value, to Php value');
        }
    }
}
