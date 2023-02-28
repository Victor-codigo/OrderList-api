<?php

declare(strict_types=1);

namespace Common\Domain\ModuleCommunication;

use Common\Domain\Config\AppConfig;

class ModuleCommunicationFactory
{
    private const API_VERSION = AppConfig::API_VERSION;

    /**
     * @param string[] $usersId
     */
    public static function userGet(array $usersId): ModuleCommunicationConfigDto
    {
        $parameters = [
            'api_version' => static::API_VERSION,
            'users_id' => implode(',', $usersId),
        ];

        return new ModuleCommunicationConfigDto(
            'user_get',
            'GET',
            $parameters,
            'application/json',
            [],
            true
        );
    }
}
