<?php

declare(strict_types=1);

namespace Common\Domain\Config;

use Common\Domain\Model\ValueObject\Constraints\VALUE_OBJECTS_CONSTRAINTS;

class AppConfig
{
    /**
     * Maximum number of users per group.
     */
    public const GROUP_USERS_MAX = 100;

    /**
     * Maximum number of users can be added to a group per request.
     */
    public const ENDPOINT_GROUP_USER_ADD_MAX_USERS = 50;

    /**
     * Maximum number of users can role be changed per request.
     */
    public const ENDPOINT_GROUP_USER_ROLE_CHANGE_MAX_USERS = 50;

    /**
     * Maximum number of users can get, per request.
     */
    public const ENDPOINT_GROUP_GET_DATA_MAX_USERS = 50;

    /**
     * Maximum number of users can get, per request.
     */
    public const ENDPOINT_GROUP_GET_USERS_MAX_USERS = 50;

    /**
     * Maximum number of notifications can be removed per request.
     */
    public const ENDPOINT_NOTIFICATION_REMOVE_MAX = 100;

    /**
     * Maximum number of products can be removed per request.
     */
    public const ENDPOINT_PRODUCT_GET_PRODUCTS_MAX = 100;

    /**
     * Maximum number of shops can be removed per request.
     */
    public const ENDPOINT_PRODUCT_GET_SHOPS_MAX = 100;

    /**
     * Maximum number of shops can be removed per request.
     */
    public const ENDPOINT_SHOP_GET_SHOPS_MAX = 100;

    /**
     * Maximum number of products can be removed per request.
     */
    public const ENDPOINT_SHOP_GET_PRODUCTS_MAX = 100;

    /**
     * Maximum number of orders can be obtained per request.
     */
    public const ENDPOINT_ORDER_GET_MAX = 100;

    /**
     * Maximum number of orders can be removed per request.
     */
    public const ENDPOINT_ORDER_REMOVE_MAX = 100;

    /**
     * Version of the api used.
     */
    public const API_VERSION = 1;

    /**
     * Name of the cookie for the token session.
     */
    public const COOKIE_SESSION_NAME = 'TOKENSESSION';

    /**
     * Module communication roxy URL.
     */
    public const MODULE_COMMUNICATION_REQUEST_PROXY = 'http://proxy:80';

    /**
     * Module communication HTTPS.
     */
    public const MODULE_COMMUNICATION_REQUEST_HTTPS = [
        'verify_peer' => false,
        'verify_host' => false,
    ];

    /**
     * Time in seconds for the token of the api calls.
     */
    public const API_TOKEN_REQUEST_EXPIRE_TIME = 300;

    /**
     * Api domain.
     */
    public const API_DOMAIN = 'orderlist.api';

    /**
     * Api protocol.
     */
    public const API_PROTOCOL = 'http';

    /**
     * Message Error for error 404.
     */
    public const ERROR_404_MESSAGE = 'Not found: error 404';

    /**
     * Message error for error 403.
     */
    public const ERROR_403_MESSAGE = 'Access denied: error 403';

    /**
     * Message error for error 500.
     */
    public const ERROR_500_MESSAGE = 'Internal server error: error 500';

    /**
     * Message error for method not allowed.
     */
    public const ERROR_METHOD_NOT_ALLOWED = 'Method not allowed';

    /**
     * Maximum number of items per page in pagination.
     */
    public const PAGINATION_PAGE_ITEMS_MAX = 100;

    protected static AppConfig|null $instance = null;
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
