<?php

declare(strict_types=1);

namespace Common\Domain\ModuleCommunication;

use Common\Domain\Config\AppConfig;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Group\Domain\Model\GROUP_TYPE;

class ModuleCommunicationFactory
{
    private const API_VERSION = AppConfig::API_VERSION;
    private const CONTENT_TYPE_APPLICATION_JSON = 'application/json';
    private const CONTENT_TYPE_APPLICATION_FORM = 'multipart/form-data';

    public static function userLogin(string $email, string $password): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => static::API_VERSION,
        ];

        $content = [
            'username' => $email,
            'password' => $password,
        ];

        return new ModuleCommunicationConfigDto(
            'user_login',
            'POST',
            $attributes,
            [],
            [],
            self::CONTENT_TYPE_APPLICATION_JSON,
            $content,
            [],
            false
        );
    }

    /**
     * @param string[] $usersId
     */
    public static function userGet(array $usersId): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => static::API_VERSION,
            'users_id' => implode(',', $usersId),
        ];

        return new ModuleCommunicationConfigDto(
            'user_get',
            'GET',
            $attributes,
            [],
            [],
            self::CONTENT_TYPE_APPLICATION_JSON,
            [],
            [],
            true
        );
    }

    /**
     * @param UploadedFileInterface[] $files
     */
    public static function groupCreate(string $name, string $description, GROUP_TYPE $type, array $files = []): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => static::API_VERSION,
        ];

        $content = [
            'name' => $name,
            'description' => $description,
            'type' => $type->value,
        ];

        return new ModuleCommunicationConfigDto(
            'group_create',
            'POST',
            $attributes,
            [],
            $files,
            self::CONTENT_TYPE_APPLICATION_FORM,
            $content,
            [],
            true
        );
    }
}
