<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Array;

interface ValueObjectArrayFactoryInterface
{
    /**
     * @param USER_ROLES[]|null $roles
     */
    public static function createRoles(?array $roles): Roles;

    public static function createNotificationData(?array $data): NotificationData;
}
