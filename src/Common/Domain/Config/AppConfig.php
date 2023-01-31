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
