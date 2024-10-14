<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Array;

use Common\Domain\Model\ValueObject\Object\Rol;

class valueObjectArrayFactory
{
    /**
     * @param Rol[]|null $roles
     */
    public static function createRoles(?array $roles): Roles
    {
        return new Roles($roles);
    }

    /**
     * @param array<string, string> $data
     */
    public static function createNotificationData(?array $data): NotificationData
    {
        return new NotificationData($data);
    }
}
