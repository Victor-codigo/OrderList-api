<?php

declare(strict_types=1);

namespace Test\Fixtures\Helpers;

use Faker\Provider\Base;

class AliceBundleHelpers extends Base
{
    /**
     * @return array<string, string>
     */
    public static function getNotificationUserRegisteredData(): array
    {
        return [
            'user_name' => 'USER NAME',
            'domain_name' => 'DOMAIN_NAME',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getNotificationGroupUserAddData(): array
    {
        return [
            'group_name' => 'GROUP NAME',
            'user_who_adds_you_name' => 'USER WHO ADDS YOU NAME',
        ];
    }
}
