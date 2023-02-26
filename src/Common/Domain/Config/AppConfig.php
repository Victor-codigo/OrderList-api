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
     * Version of the api used.
     */
    public const API_VERSION = 1;

    /**
     * Time in seconds for the token of the api calls.
     */
    public const API_TOKEN_RESQUEST_EXPIRE_TIME = 300;

    /**
     * Api domina.
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
