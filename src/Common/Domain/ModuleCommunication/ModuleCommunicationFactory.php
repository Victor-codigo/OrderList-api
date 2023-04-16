<?php

declare(strict_types=1);

namespace Common\Domain\ModuleCommunication;

use Common\Domain\Config\AppConfig;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Group\Domain\Model\GROUP_TYPE;
use Notification\Domain\Model\NOTIFICATION_TYPE;

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
     * @param string[] $usersId
     */
    public static function userGetByName(array $usersNames): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => static::API_VERSION,
            'users_name' => implode(',', $usersNames),
        ];

        return new ModuleCommunicationConfigDto(
            'user_get_by_name',
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

    public static function notificationCreateUserRegistered(string $recipientUserId, string $userName, string $domainName, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => $recipientUserId,
            'type' => NOTIFICATION_TYPE::USER_REGISTERED->value,
            'notification_data' => [
                'user_name' => $userName,
                'domain_name' => $domainName,
            ],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content);
    }

    public static function notificationCreateGroupUserAdded(array $recipientUsersId, string $groupName, string $userWhoAddsYouName, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => $recipientUsersId,
            'type' => NOTIFICATION_TYPE::GROUP_USER_ADDED->value,
            'notification_data' => [
                'group_name' => $groupName,
                'user_who_adds_you_name' => $userWhoAddsYouName,
            ],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content);
    }

    private static function notificationCreate(array $content): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => static::API_VERSION,
        ];

        return new ModuleCommunicationConfigDto(
            'notification_create',
            'POST',
            $attributes,
            [],
            [],
            self::CONTENT_TYPE_APPLICATION_JSON,
            $content,
            [],
            true
        );
    }
}
