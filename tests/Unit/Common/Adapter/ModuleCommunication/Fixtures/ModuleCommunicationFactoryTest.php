<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\ModuleCommunication\Fixtures;

use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;

class ModuleCommunicationFactoryTest
{
    /**
     * @param string[] $usersId
     */
    public static function json(array $content = [], array $query = [], array $cookies = [], $authentication = false): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => 1,
        ];

        return new ModuleCommunicationConfigDto(
            'user_get',
            'GET',
            $attributes,
            $query,
            [],
            'application/json',
            $content,
            $cookies,
            $authentication
        );
    }

    /**
     * @param UploadedFileInterface[] $files
     */
    public static function form(array $content = [], array $query = [], array $files = [], array $cookies = [], $authentication = false): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => 1,
        ];

        return new ModuleCommunicationConfigDto(
            'route',
            'POST',
            $attributes,
            $query,
            $files,
            'multipart/form-data',
            $content,
            $cookies,
            $authentication
        );
    }
}
