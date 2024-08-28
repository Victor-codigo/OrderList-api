<?php

declare(strict_types=1);

namespace Common\Domain\Config;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;

class AppConfig
{
    /**
     * Maximum number of users per group.
     */
    public const int GROUP_USERS_MAX = 100;

    /**
     * Maximum number of orders per list of orders.
     */
    public const int LIST_ORDERS_MAX_ORDERS = 500;

    /**
     * Maximum number of users can be added to a group per request.
     */
    public const int ENDPOINT_GROUP_USER_ADD_MAX_USERS = 50;

    /**
     * Maximum number of users can be removed to a group per request.
     */
    public const int ENDPOINT_GROUP_USER_REMOVE_MAX_USERS = 50;

    /**
     * Maximum number of users can role be changed per request.
     */
    public const int ENDPOINT_GROUP_USER_ROLE_CHANGE_MAX_USERS = 50;

    /**
     * Maximum number of users can get, per request.
     */
    public const int ENDPOINT_GROUP_GET_DATA_MAX_GROUPS = 50;

    /**
     * Maximum number of admins can get, per request.
     */
    public const int ENDPOINT_GROUP_GET_GROUPS_ADMINS_MAX = 100;

    /**
     * Maximum number of groups can be deleted, per request.
     */
    public const int ENDPOINT_GROUP_DELETE_MAX = 50;

    /**
     * Maximum number of users can get, per request.
     */
    public const int ENDPOINT_GROUP_GET_USERS_MAX_USERS = 50;

    /**
     * Maximum number of notifications can be removed per request.
     */
    public const int ENDPOINT_NOTIFICATION_REMOVE_MAX = 100;

    /**
     * Maximum number of notifications can be removed per request.
     */
    public const int ENDPOINT_NOTIFICATION_MARK_AS_VIEWED_MAX = 100;

    /**
     * Maximum number of products can be removed per request.
     */
    public const int ENDPOINT_PRODUCT_GET_PRODUCTS_MAX = 100;

    /**
     * Maximum number of shops can be removed per request.
     */
    public const int ENDPOINT_PRODUCT_GET_SHOPS_MAX = 100;

    /**
     * Maximum number of shops and prices can be set by request.
     */
    public const int ENDPOINT_PRODUCT_PATCH_PRICES_SHOPS_MAX = 50;

    /**
     * Maximum number of shops can be removed per request.
     */
    public const int ENDPOINT_SHOP_GET_SHOPS_MAX = 100;

    /**
     * Maximum number of products can be removed per request.
     */
    public const int ENDPOINT_SHOP_GET_PRODUCTS_MAX = 100;

    /**
     * Maximum number of shops can be removed per request.
     */
    public const int ENDPOINT_SHOP_REMOVE_MAX = 100;

    /**
     * Maximum number of orders can be obtained per request.
     */
    public const int ENDPOINT_ORDER_GET_MAX = 100;

    /**
     * Maximum number of orders can be removed per request.
     */
    public const int ENDPOINT_ORDER_REMOVE_MAX = 100;

    /**
     * Maximum number of list orders can get data per request.
     */
    public const int ENDPOINT_LIST_ORDERS_GET_DATA_MAX = 100;

    /**
     * Maximum number of orders can added to a list of orders per request.
     */
    public const int ENDPOINT_LIST_ORDERS_ADD_ORDERS_MAX = 100;

    /**
     * Maximum number of list orders can removed per request.
     */
    public const int ENDPOINT_LIST_ORDERS_REMOVE_MAX = 100;

    /**
     * Version of the api used.
     */
    public const string API_VERSION = '1';

    /**
     * Name of the cookie for the token session.
     */
    public const string COOKIE_SESSION_NAME = 'TOKENSESSION';

    /**
     * Module communication proxy configuration.
     */
    public const array MODULE_COMMUNICATION_PROXY_CONFIG = [
        // 'proxy' => 'http://proxy:80',
        // 'verify_peer' => false,
        // 'verify_host' => false,
    ];

    /**
     * Message Error for error 404.
     */
    public const string ERROR_404_MESSAGE = 'Not found: error 404';

    /**
     * Message error for error 403.
     */
    public const string ERROR_403_MESSAGE = 'Access denied: error 403';

    /**
     * Message error for error 500.
     */
    public const string ERROR_500_MESSAGE = 'Internal server error: error 500';

    /**
     * Message error for method not allowed.
     */
    public const string ERROR_METHOD_NOT_ALLOWED = 'Method not allowed';

    /**
     * Maximum number of items per page in pagination.
     */
    public const int PAGINATION_PAGE_ITEMS_MAX = 100;

    /**
     * Maximum size of user image width.
     */
    public const int USER_IMAGE_FRAME_SIZE_WIDTH = 300;

    /**
     * Maximum size of user image height.
     */
    public const int USER_IMAGE_FRAME_SIZE_HEIGHT = 300;

    /**
     * Maximum size of product image width.
     */
    public const int PRODUCT_IMAGE_FRAME_SIZE_WIDTH = 300;

    /**
     * Maximum size of product image height.
     */
    public const int PRODUCT_IMAGE_FRAME_SIZE_HEIGHT = 300;

    /**
     * Maximum size of shop image width.
     */
    public const int SHOP_IMAGE_FRAME_SIZE_WIDTH = 300;

    /**
     * Maximum size of shop image height.
     */
    public const int SHOP_IMAGE_FRAME_SIZE_HEIGHT = 300;

    /**
     * Routes tha the user Guest is allowed to use.
     */
    public const array USER_GUEST_ROUTES_NAMES_ALLOWED = [
        'user_get',
        'user_get_by_name',
        'group_create',
        'group_remove',
        'group_modify',
        'group_user_get_groups',
        'group_group_get_users',
        'group_get_admins',
        'group_get_groups_admins',
        'group_get_data',
        'group_get_data_by_name',
        'group_user_remove',
        'group_user_role_change',
        'notification_create',
        'notification_user_remove_all',
        'notification_get_data',
        'notification_mark_as_viewed',
        'product_create',
        'product_modify',
        'product_remove',
        'product_get_data',
        'product_set_price',
        'product_get_price',
        'product_get_first_letter',
        'shop_create',
        'shop_modify',
        'shop_remove',
        'shop_get_data',
        'shop_get_shops_first_letter',
        'order_create',
        'order_modify',
        'order_remove',
        'order_get_data',
        'orders_bought',
        'list_orders_create',
        'list_orders_create_from',
        'list_orders_modify',
        'list_orders_get_data',
        'list_orders_remove',
        'list_orders_get_price',
        'list_orders_get_first_letter',
    ];

    protected static ?AppConfig $instance = null;
    protected static VALUE_OBJECTS_CONSTRAINTS $valueObject;

    public static function valueObject(): VALUE_OBJECTS_CONSTRAINTS
    {
        return static::getInstance()::$valueObject;
    }

    protected function __construct()
    {
        static::$valueObject = new VALUE_OBJECTS_CONSTRAINTS();
    }

    protected static function getInstance(): static
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
