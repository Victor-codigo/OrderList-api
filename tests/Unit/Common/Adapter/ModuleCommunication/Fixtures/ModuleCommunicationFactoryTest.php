<?php

declare(strict_types=1);

namespace Test\Unit\Common\Adapter\ModuleCommunication\Fixtures;

use Common\Domain\ModuleCommunication\ModuleCommunicationConfigDto;

class ModuleCommunicationFactoryTest
{
    /**
     * @param string[] $usersId
     */
    public static function json(bool $authentication, array $content = [], array $query = [], array $files = [], array $cookies = [], array $headers = []): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => 1,
        ];

        return new ModuleCommunicationConfigDto(
            'user_get',
            'GET',
            $authentication,
            $attributes,
            $query,
            $files,
            'application/json',
            $content,
            $cookies,
            $headers
        );
    }

    /**
     * @param UploadedFileInterface[] $files
     */
    public static function form(bool $authentication, array $content = [], array $query = [], array $files = [], array $cookies = [], array $headers = []): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => 1,
        ];

        return new ModuleCommunicationConfigDto(
            'route',
            'POST',
            $authentication,
            $attributes,
            $query,
            $files,
            'multipart/form-data',
            $content,
            $cookies,
            $headers
        );
    }
}
