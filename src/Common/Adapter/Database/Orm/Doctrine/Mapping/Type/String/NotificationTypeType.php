<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Mapping\Type\String;

use Common\Adapter\Database\Orm\Doctrine\Mapping\Type\TypeBase;
use Common\Domain\Exception\InvalidArgumentException;
use Common\Domain\Model\ValueObject\Object\NotificationType;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class NotificationTypeType extends TypeBase
{
    #[\Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return sprintf('VARCHAR(%d)', $column['length']);
    }

    #[\Override]
    public function getClassImplementationName(): string
    {
        return NotificationType::class;
    }

    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        /** @var ?NOTIFICATION_TYPE $notificationType */
        $notificationType = parent::convertToDatabaseValue($value, $platform);

        return null === $notificationType ? null : $notificationType->value;
    }

    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        try {
            $notificationType = NOTIFICATION_TYPE::from($value);

            return parent::convertToPHPValue($notificationType, $platform);
        } catch (\Error) {
            throw InvalidArgumentException::fromMessage('NotificationTypeTpe: Could not convert from database value, to Php value');
        }
    }
}
