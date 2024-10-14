<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Array;

use Common\Domain\Validation\User\USER_ROLES;

interface ValueObjectArrayFactoryInterface
{
    /**
     * @param USER_ROLES[]|null $roles
     */
    public static function createRoles(?array $roles): Roles;

    /**
     * @param array<string, string>|null $data $data
     */
    public static function createNotificationData(?array $data): NotificationData;
}
