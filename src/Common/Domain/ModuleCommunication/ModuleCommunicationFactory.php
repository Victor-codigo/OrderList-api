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
use Common\Domain\Model\ValueObject\String\Language;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Ports\FileUpload\UploadedFileInterface;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;

class ModuleCommunicationFactory
{
    private const string API_VERSION = AppConfig::API_VERSION;
    private const string COOKIE_SESSION_NAME = AppConfig::COOKIE_SESSION_NAME;
    private const string CONTENT_TYPE_APPLICATION_JSON = 'application/json';
    private const string CONTENT_TYPE_APPLICATION_FORM = 'multipart/form-data';

    public static function userLogin(Email $email, Password $password): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => self::API_VERSION,
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
            fn (Identifier $userId): ?string => $userId->getValue(),
            $usersId
        );

        $attributes = [
            'api_version' => self::API_VERSION,
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
            fn (Identifier $userId): ?string => $userId->getValue(),
            $usersId
        );

        $attributes = [
            'api_version' => self::API_VERSION,
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
     * @param NameWithSpaces[] $usersNames
     */
    public static function userGetByName(array $usersNames): ModuleCommunicationConfigDto
    {
        $usersNamePlain = array_map(
            fn (NameWithSpaces $userName): ?string => $userName->getValue(),
            $usersNames
        );

        $attributes = [
            'api_version' => self::API_VERSION,
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
    public static function groupCreate(NameWithSpaces $name, Description $description, GROUP_TYPE $type, array $files, ?bool $notifyUser): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => self::API_VERSION,
        ];

        $content = [
            'name' => $name->getValue(),
            'type' => $type->value,
            'notify' => ($notifyUser ?? true) ? 'true' : 'false',
        ];

        if (!$description->isNull()) {
            $content['description'] = $description->getValue();
        }

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
    public static function groupsRemove(array $groupsId): ModuleCommunicationConfigDto
    {
        $groupsIdString = array_map(
            fn (Identifier $groupId): ?string => $groupId->getValue(),
            $groupsId
        );

        $attributes = [
            'api_version' => self::API_VERSION,
        ];

        $content = [
            'groups_id' => $groupsIdString,
        ];

        return new ModuleCommunicationConfigDto(
            'group_remove',
            'DELETE',
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

    /**
     * @param Identifier[] $groupsId
     */
    public static function groupGetData(array $groupsId): ModuleCommunicationConfigDto
    {
        $groupsIdPlain = array_map(
            fn (Identifier $groupId): ?string => $groupId->getValue(),
            $groupsId
        );

        $attributes = [
            'api_version' => self::API_VERSION,
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
            'api_version' => self::API_VERSION,
            'group_id' => $groupsId->getValue(),
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

    public static function groupGetAdmins(Identifier $groupId): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => self::API_VERSION,
            'group_id' => $groupId->getValue(),
        ];

        return new ModuleCommunicationConfigDto(
            'group_get_admins',
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

    /**
     * @param Identifier[] $groupsId
     */
    public static function groupGetGroupsAdmins(array $groupsId, PaginatorPage $page, PaginatorPageItems $pageItems): ModuleCommunicationConfigDto
    {
        $groupsIdString = array_map(
            fn (Identifier $groupId): ?string => $groupId->getValue(),
            $groupsId
        );

        $attributes = [
            'api_version' => self::API_VERSION,
            'groups_id' => implode(',', $groupsIdString),
        ];
        $query = [
            'page' => $page->getValue(),
            'page_items' => $pageItems->getValue(),
        ];

        return new ModuleCommunicationConfigDto(
            'group_get_groups_admins',
            'GET',
            true,
            $attributes,
            $query,
            [],
            self::CONTENT_TYPE_APPLICATION_JSON,
            [],
            [],
            []
        );
    }

    public static function groupUserGetGroups(?GROUP_TYPE $groupType, ?string $filterSection, ?string $filterText, ?string $filterValue, PaginatorPage $page, PaginatorPageItems $pageItems, bool $orderAsc): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => self::API_VERSION,
        ];

        $query = [
            'page' => $page->getValue(),
            'page_items' => $pageItems->getValue(),
            'order_asc' => $orderAsc ? 'true' : 'false',
        ];

        if (null !== $groupType) {
            $query['group_type'] = $groupType->value;
        }

        if (null !== $filterSection) {
            $query['filter_section'] = $filterSection;
        }

        if (null !== $filterText) {
            $query['filter_text'] = $filterText;
        }

        if (null !== $filterValue) {
            $query['filter_value'] = $filterValue;
        }

        return new ModuleCommunicationConfigDto(
            'group_user_get_groups',
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

    /**
     * @param Identifier[] $usersId
     */
    public static function groupUserRemove(Identifier $groupId, array $usersId): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => self::API_VERSION,
        ];

        $content = [
            'group_id' => $groupId->getValue(),
            'users_id' => array_map(
                fn (Identifier $userId): ?string => $userId->getValue(),
                $usersId
            ),
        ];

        return new ModuleCommunicationConfigDto(
            'group_user_remove',
            'DELETE',
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

    public static function groupRemoveAllUserGroups(string $systemKey): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => self::API_VERSION,
        ];
        $content = [
            'system_key' => $systemKey,
        ];

        return new ModuleCommunicationConfigDto(
            'group_user_remove_all_groups',
            'DELETE',
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

    /**
     * @param string[] $productsId
     * @param string[] $shopsId
     */
    public static function productGetData(Identifier $groupsId, array $productsId, array $shopsId = [], string $productNameStartsWith = ''): ModuleCommunicationConfigDto
    {
        $query = [
            'api_version' => self::API_VERSION,
            'group_id' => $groupsId->getValue(),
        ];

        if (!empty($productsId)) {
            $query['products_id'] = implode(',', $productsId);
        }

        if (!empty($shopsId)) {
            $query['shops_id'] = implode(',', $shopsId);
        }

        if (!empty($productNameStartsWith)) {
            $query['product_name_starts_with'] = $productNameStartsWith;
        }

        return new ModuleCommunicationConfigDto(
            'product_get_data',
            'GET',
            true,
            [],
            $query,
            [],
            self::CONTENT_TYPE_APPLICATION_FORM,
            [],
            [],
            []
        );
    }

    /**
     * @param Identifier[] $groupsId
     */
    public static function productRemoveGroupsProducts(array $groupsId, string $systemKey): ModuleCommunicationConfigDto
    {
        $query = [
            'api_version' => self::API_VERSION,
        ];

        $content = [
            'groups_id' => array_map(
                fn (Identifier $groupId): ?string => $groupId->getValue(),
                $groupsId
            ),
            'system_key' => $systemKey,
        ];

        return new ModuleCommunicationConfigDto(
            'product_remove_groups_products',
            'DELETE',
            true,
            [],
            $query,
            [],
            self::CONTENT_TYPE_APPLICATION_JSON,
            $content,
            [],
            []
        );
    }

    /**
     * @param string[] $shopsId
     * @param string[] $productsId
     */
    public static function shopGetData(Identifier $groupsId, array $shopsId, array $productsId = [], string $shopNameStartsWith = ''): ModuleCommunicationConfigDto
    {
        $query = [
            'api_version' => self::API_VERSION,
            'group_id' => $groupsId->getValue(),
        ];

        if (!empty($productsId)) {
            $query['products_id'] = implode(',', $productsId);
        }

        if (!empty($shopsId)) {
            $query['shops_id'] = implode(',', $shopsId);
        }

        if (!empty($shopNameStartsWith)) {
            $query['shop_name_starts_with'] = $shopNameStartsWith;
        }

        return new ModuleCommunicationConfigDto(
            'shop_get_data',
            'GET',
            true,
            [],
            $query,
            [],
            self::CONTENT_TYPE_APPLICATION_FORM,
            [],
            [],
            []
        );
    }

    /**
     * @param Identifier[] $groupsId
     */
    public static function shopRemoveGroupsShops(array $groupsId, string $systemKey): ModuleCommunicationConfigDto
    {
        $query = [
            'api_version' => self::API_VERSION,
        ];

        $content = [
            'groups_id' => array_map(
                fn (Identifier $groupId): ?string => $groupId->getValue(),
                $groupsId
            ),
            'system_key' => $systemKey,
        ];

        return new ModuleCommunicationConfigDto(
            'shop_remove_groups_shops',
            'DELETE',
            true,
            [],
            $query,
            [],
            self::CONTENT_TYPE_APPLICATION_JSON,
            $content,
            [],
            []
        );
    }

    /**
     * @param Identifier[] $recipientUsersId
     */
    public static function notificationCreateUserRegistered(array $recipientUsersId, NameWithSpaces $userName, string $domainName, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => array_map(
                fn (Identifier $userId): ?string => $userId->getValue(),
                $recipientUsersId
            ),
            'type' => NOTIFICATION_TYPE::USER_REGISTERED->value,
            'notification_data' => [
                'user_name' => $userName->getValue(),
                'domain_name' => $domainName,
            ],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content, null);
    }

    /**
     * @param Identifier[] $recipientUsersId
     */
    public static function notificationCreateGroupUserAdded(array $recipientUsersId, NameWithSpaces $groupName, NameWithSpaces $userWhoAddsYouName, string $systemKey): ModuleCommunicationConfigDto
    {
        $recipientUsersIdPlain = array_map(
            fn (Identifier $recipientUserId): ?string => $recipientUserId->getValue(),
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

        return self::notificationCreate($content, null);
    }

    /**
     * @param Identifier[] $recipientUsersId
     */
    public static function notificationCreateGroupCreated(array $recipientUsersId, NameWithSpaces $groupName, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => array_map(
                fn (Identifier $userId): ?string => $userId->getValue(),
                $recipientUsersId
            ),
            'type' => NOTIFICATION_TYPE::GROUP_CREATED->value,
            'notification_data' => [
                'group_name' => $groupName->getValue(),
            ],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content, null);
    }

    /**
     * @param Identifier[] $recipientUsersId
     */
    public static function notificationCreateGroupRemoved(array $recipientUsersId, NameWithSpaces $groupName, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => array_map(
                fn (Identifier $userId): ?string => $userId->getValue(),
                $recipientUsersId
            ),
            'type' => NOTIFICATION_TYPE::GROUP_REMOVED->value,
            'notification_data' => [
                'group_name' => $groupName->getValue(),
            ],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content, null);
    }

    /**
     * @param Identifier[] $recipientUsersId
     */
    public static function notificationCreateGroupUserSetAsAdmin(array $recipientUsersId, NameWithSpaces $groupName, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => array_map(
                fn (Identifier $userId): ?string => $userId->getValue(),
                $recipientUsersId
            ),
            'type' => NOTIFICATION_TYPE::GROUP_USER_SET_AS_ADMIN->value,
            'notification_data' => [
                'group_name' => $groupName->getValue(),
            ],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content, null);
    }

    /**
     * @param Identifier[] $usersId
     */
    public static function notificationCreateGroupUsersRemoved(array $usersId, NameWithSpaces $groupName, string $systemKey): ModuleCommunicationConfigDto
    {
        $usersIdPlain = array_map(
            fn (Identifier $userId): ?string => $userId->getValue(),
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

        return self::notificationCreate($content, null);
    }

    /**
     * @param Identifier[] $recipientUsersId
     */
    public static function notificationUserEmailChanged(array $recipientUsersId, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => array_map(
                fn (Identifier $userId): ?string => $userId->getValue(),
                $recipientUsersId
            ),
            'type' => NOTIFICATION_TYPE::USER_EMAIL_CHANGED->value,
            'notification_data' => [],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content, null);
    }

    /**
     * @param Identifier[] $recipientUsersId
     */
    public static function notificationUserPasswordChanged(array $recipientUsersId, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => array_map(
                fn (Identifier $userId): ?string => $userId->getValue(),
                $recipientUsersId
            ),
            'type' => NOTIFICATION_TYPE::USER_PASSWORD_CHANGED->value,
            'notification_data' => [],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content, null);
    }

    /**
     * @param Identifier[] $recipientUsersId
     */
    public static function notificationUserPasswordRemember(array $recipientUsersId, JwtToken $tokenSession, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => array_map(
                fn (Identifier $userId): ?string => $userId->getValue(),
                $recipientUsersId
            ),
            'type' => NOTIFICATION_TYPE::USER_PASSWORD_REMEMBER->value,
            'notification_data' => [],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content, $tokenSession);
    }

    public static function notificationShareListOrdersCreated(Identifier $userId, Identifier $sharedRecourseId, NameWithSpaces $listOrdersName, string $systemKey): ModuleCommunicationConfigDto
    {
        $content = [
            'users_id' => [$userId->getValue()],
            'type' => NOTIFICATION_TYPE::SHARE_LIST_ORDERS_CREATED->value,
            'notification_data' => [
                'shared_recourse_id' => $sharedRecourseId->getValue(),
                'list_orders_name' => $listOrdersName->getValue(),
            ],
            'system_key' => $systemKey,
        ];

        return self::notificationCreate($content, null);
    }

    /**
     * @param array<int|string, mixed> $content
     */
    private static function notificationCreate(array $content, ?JwtToken $tokenSession): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => self::API_VERSION,
        ];

        if (null !== $tokenSession && !$tokenSession->isNull()) {
            $headers = [
                'Authorization' => "Bearer {$tokenSession->getValue()}",
            ];
        }

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
            $headers ?? []
        );
    }

    public static function notificationsUserGetData(PaginatorPage $page, PaginatorPageItems $pageItems, Language $lang): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => self::API_VERSION,
        ];

        $query = [
            'page' => $page->getValue(),
            'page_items' => $pageItems->getValue(),
            'lang' => $lang->getValue(),
        ];

        return new ModuleCommunicationConfigDto(
            'notification_get_data',
            'GET',
            true,
            $attributes,
            $query,
            [],
            self::CONTENT_TYPE_APPLICATION_JSON,
            [],
            [],
            []
        );
    }

    /**
     * @param Identifier[] $notificationsId
     */
    public static function notificationsRemove(array $notificationsId): ModuleCommunicationConfigDto
    {
        $notificationsIdString = array_map(
            fn (Identifier $notificationId): ?string => $notificationId->getValue(),
            $notificationsId
        );

        $attributes = [
            'api_version' => self::API_VERSION,
            'notifications_id' => implode(',', $notificationsIdString),
        ];

        return new ModuleCommunicationConfigDto(
            'notification_remove',
            'DELETE',
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

    public static function notificationsRemoveAllUserNotifications(string $systemKey): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => self::API_VERSION,
        ];
        $content = [
            'system_key' => $systemKey,
        ];

        return new ModuleCommunicationConfigDto(
            'notification_user_remove_all',
            'DELETE',
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

    /**
     * @param Identifier[] $groupsIdToRemove
     * @param Identifier[] $groupsIdToChangeUserId
     */
    public static function ordersRemoveAllUserOrdersOrChangeUserId(array $groupsIdToRemove, array $groupsIdToChangeUserId, string $systemKey): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => self::API_VERSION,
        ];

        $content = [
            'groups_id_remove' => array_map(
                fn (Identifier $groupId): ?string => $groupId->getValue(),
                $groupsIdToRemove
            ),
            'groups_id_change_user_id' => array_map(
                fn (Identifier $groupId): ?string => $groupId->getValue(),
                $groupsIdToChangeUserId
            ),
            'system_key' => $systemKey,
        ];

        return new ModuleCommunicationConfigDto(
            'order_remove_all_group_id_and_change_user_id',
            'DELETE',
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

    /**
     * @param Identifier[] $groupsIdToRemove
     * @param Identifier[] $groupsIdToChangeUserId
     */
    public static function listOrdersRemoveAllUserListOrdersOrChangeUserId(array $groupsIdToRemove, array $groupsIdToChangeUserId, string $systemKey): ModuleCommunicationConfigDto
    {
        $attributes = [
            'api_version' => self::API_VERSION,
        ];

        $content = [
            'groups_id_remove' => array_map(
                fn (Identifier $groupId): ?string => $groupId->getValue(),
                $groupsIdToRemove
            ),
            'groups_id_change_user_id' => array_map(
                fn (Identifier $groupId): ?string => $groupId->getValue(),
                $groupsIdToChangeUserId
            ),
            'system_key' => $systemKey,
        ];

        return new ModuleCommunicationConfigDto(
            'list_order_remove_all_group_id_and_change_user_id',
            'DELETE',
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
