<?php

declare(strict_types=1);

namespace Common\Domain\ModuleCommunication;

use Common\Domain\Config\AppConfig;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Group\Domain\Model\GROUP_TYPE;

class ModuleCommunicationFactory
{
    private const API_VERSION = AppConfig::API_VERSION;
    private const CONTENT_TYPE_APPLICATION_JSON = 'application/json';
    private const CONTENT_TYPE_APPLICATION_FORM = 'multipart/form-data';

    public static function userLogin(Email $email, Password $password): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => static::API_VERSION,
        ];

        $content = [
            'username' => $email->getValue(),
            'password' => $password->getValue(),
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
     * @param Identifier[] $usersId
     */
    public static function userGet(array $usersId): ModuleCommunicationConfigDto
    {
        $usersIdPlain = array_map(
            fn (Identifier $userId) => $userId->getValue(),
            $usersId
        );

        $attributes = [
            'api_version' => static::API_VERSION,
            'users_id' => implode(',', $usersIdPlain),
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
     * @param Name[] $usersId
     */
    public static function userGetByName(array $usersNames): ModuleCommunicationConfigDto
    {
        $usersNamePlain = array_map(
            fn (Name $userName) => $userName->getValue(),
            $usersNames
        );

        $attributes = [
            'api_version' => static::API_VERSION,
            'users_name' => implode(',', $usersNamePlain),
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
    public static function groupCreate(Name $name, Description $description, GROUP_TYPE $type, array $files = []): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => static::API_VERSION,
        ];

        $content = [
            'name' => $name->getValue(),
            'description' => $description->getValue(),
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

    public static function notificationCreateUserRegistered(Identifier $recipientUserId, Name $userName, string $domainName, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => $recipientUserId->getValue(),
            'type' => NOTIFICATION_TYPE::USER_REGISTERED->value,
            'notification_data' => [
                'user_name' => $userName->getValue(),
                'domain_name' => $domainName,
            ],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content);
    }

    /**
     * @param Identifier[] $recipientUsersId
     */
    public static function notificationCreateGroupUserAdded(array $recipientUsersId, Name $groupName, Name $userWhoAddsYouName, string $systemKey): ModuleCommunicationConfigDto
    {
        $recipientUsersIdPlain = array_map(
            fn (Identifier $recipientUserId) => $recipientUserId->getValue(),
            $recipientUsersId
        );

        $content = [
            'users_id' => $recipientUsersIdPlain,
            'type' => NOTIFICATION_TYPE::GROUP_USER_ADDED->value,
            'notification_data' => [
                'group_name' => $groupName->getValue(),
                'user_who_adds_you_name' => $userWhoAddsYouName->getValue(),
            ],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content);
    }

    public static function notificationCreateGroupCreated(Identifier $userId, Name $groupName, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => [$userId->getValue()],
            'type' => NOTIFICATION_TYPE::GROUP_CREATED->value,
            'notification_data' => [
                'group_name' => $groupName->getValue(),
            ],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content);
    }

    public static function notificationCreateGroupRemoved(Identifier $userId, Name $groupName, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => [$userId->getValue()],
            'type' => NOTIFICATION_TYPE::GROUP_REMOVED->value,
            'notification_data' => [
                'group_name' => $groupName->getValue(),
            ],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content);
    }

    /**
     * @param Identifier[] $usersId
     */
    public static function notificationCreateGroupUsersRemoved(array $usersId, Name $groupName, string $systemKey): ModuleCommunicationConfigDto
    {
        $usersIdPlain = array_map(
            fn (Identifier $userId) => $userId->getValue(),
            $usersId
        );

        $content = [
            'users_id' => $usersIdPlain,
            'type' => NOTIFICATION_TYPE::GROUP_USER_REMOVED->value,
            'notification_data' => [
                'group_name' => $groupName->getValue(),
            ],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content);
    }

    /**
     * @param Identifier[] $recipientUsersId
     */
    public static function notificationUserEmailChanged(Identifier $userId, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => [$userId->getValue()],
            'type' => NOTIFICATION_TYPE::USER_EMAIL_CHANGED->value,
            'notification_data' => [],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content);
    }

    /**
     * @param Identifier[] $recipientUsersId
     */
    public static function notificationUserPasswordChanged(Identifier $userId, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => [$userId->getValue()],
            'type' => NOTIFICATION_TYPE::USER_PASSWORD_CHANGED->value,
            'notification_data' => [],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content);
    }

    /**
     * @param Identifier[] $recipientUsersId
     */
    public static function notificationUserPasswordRemember(Identifier $userId, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => [$userId->getValue()],
            'type' => NOTIFICATION_TYPE::USER_PASSWORD_REMEMBER->value,
            'notification_data' => [],
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
