<?php

declare(strict_types=1);

namespace Common\Domain\ModuleCommunication;

use Common\Domain\Config\AppConfig;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\JwtToken;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;

class ModuleCommunicationFactory
{
    private const API_VERSION = AppConfig::API_VERSION;
    private const COOKIE_SESSION_NAME = AppConfig::COOKIE_SESSION_NAME;
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
            false,
            $attributes,
            [],
            [],
            self::CONTENT_TYPE_APPLICATION_JSON,
            $content,
            [],
            [],
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
            true,
            $attributes,
            [],
            [],
            self::CONTENT_TYPE_APPLICATION_JSON,
            [],
            [],
            []
        );
    }

    /**
     * @param Identifier[] $usersId
     */
    public static function userGetWithToken(array $usersId, JwtToken $sessionToken): ModuleCommunicationConfigDto
    {
        $usersIdPlain = array_map(
            fn (Identifier $userId) => $userId->getValue(),
            $usersId
        );

        $attributes = [
            'api_version' => static::API_VERSION,
            'users_id' => implode(',', $usersIdPlain),
        ];

        $cookies = [
            self::COOKIE_SESSION_NAME => $sessionToken->getValue(),
        ];

        return new ModuleCommunicationConfigDto(
            'user_get',
            'GET',
            true,
            $attributes,
            [],
            [],
            self::CONTENT_TYPE_APPLICATION_JSON,
            [],
            $cookies,
            []
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
            true,
            $attributes,
            [],
            [],
            self::CONTENT_TYPE_APPLICATION_JSON,
            [],
            [],
            []
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
            true,
            $attributes,
            [],
            $files,
            self::CONTENT_TYPE_APPLICATION_FORM,
            $content,
            [],
            []
        );
    }

    /**
     * @param Identifier[] $groupsId
     */
    public static function groupGetData(array $groupsId): ModuleCommunicationConfigDto
    {
        $groupsIdPlain = array_map(
            fn (Identifier $groupId) => $groupId->getValue(),
            $groupsId
        );

        $attributes = [
            'api_version' => static::API_VERSION,
            'groups_id' => implode(',', $groupsIdPlain),
        ];

        return new ModuleCommunicationConfigDto(
            'group_get_data',
            'GET',
            true,
            $attributes,
            [],
            [],
            self::CONTENT_TYPE_APPLICATION_FORM,
            [],
            [],
            []
        );
    }

    public static function groupGetUsers(Identifier $groupsId, PaginatorPage $page, PaginatorPageItems $pageItems): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => static::API_VERSION,
            'group_id' => $groupsId,
        ];

        $query = [
            'page' => $page->getValue(),
            'page_items' => $pageItems->getValue(),
        ];

        return new ModuleCommunicationConfigDto(
            'group_group_get_users',
            'GET',
            true,
            $attributes,
            $query,
            [],
            self::CONTENT_TYPE_APPLICATION_FORM,
            [],
            [],
            []
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
            true,
            $attributes,
            [],
            [],
            self::CONTENT_TYPE_APPLICATION_JSON,
            $content,
            [],
            []
        );
    }
}
