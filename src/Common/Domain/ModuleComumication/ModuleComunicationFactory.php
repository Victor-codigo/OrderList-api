<?php

declare(strict_types=1);

namespace Common\Domain\ModuleComumication;

use Common\Domain\Config\AppConfig;

class ModuleComunicationFactory
{
    private const API_VERSION = AppConfig::API_VERSION;

    /**
     * @param string[] $usersId
     */
    public static function userGet(array $usersId): ModuleComunicationConfigDto
    {
        $parameters = [
            'api_version' => static::API_VERSION,
            'users_id' => implode(',', $usersId),
        ];

        return new ModuleComunicationConfigDto(
            'user_get',
            'GET',
            $parameters,
            'application/json',
            [],
            true
        );
    }
}
