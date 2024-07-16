<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Constraints;

use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Filter\FILTER_STRING_COMPARISON;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;
use Common\Domain\Validation\User\USER_ROLES;

final class VALUE_OBJECTS_CONSTRAINTS
{
    public const int ID_LENGTH = 36;
    public const string ID_TYPE = 'string';

    public const int EMAIL_LENGTH = 50;
    public const string EMAIL_TYPE = 'string';

    public const int PASSWORD_MIN_LENGTH = 6;
    public const int PASSWORD_MAX_LENGTH = 50;
    public const string PASSWORD_TYPE = 'string';

    public const int NAME_MIN_LENGTH = 1;
    public const int NAME_MAX_LENGTH = 50;
    public const string NAME_TYPE = 'string';

    public const int NAME_WITH_SPACES_MIN_LENGTH = self::NAME_MIN_LENGTH;
    public const int NAME_WITH_SPACES_MAX_LENGTH = self::NAME_MAX_LENGTH;
    public const string NAME_WITH_SPACES_TYPE = self::NAME_TYPE;

    public const int ROLES_LENGTH = 50;
    public const string ROLES_TYPE = 'json';
    public const array ROLES_VALUES = [USER_ROLES::ADMIN, USER_ROLES::USER, USER_ROLES::NOT_ACTIVE];

    public const int CREATED_ON_LENGTH = 50;
    public const string CREATED_ON_TYPE = 'json';

    public const int PATH_MIN_LENGTH = 1;
    public const int PATH_MAX_LENGTH = 256;

    public const int JWT_TOKEN_MIN_LENGTH = 36;

    /**
     * File size in Bytes.
     */
    public const int FILE_MAX_FILE_SIZE = 2 * 1_000_000;
    public const array FILE_MIME_TYPES = [];

    /**
     * Constraints for user image.
     */
    public const int FILE_USER_IMAGE_MAX_FILE_SIZE = 2 * 1_000_000;
    public const ?int FILE_USER_IMAGE_NAME_MAX_LENGTH = null;
    public const ?int FILE_USER_IMAGE_MIN_WITH = null;
    public const ?int FILE_USER_IMAGE_MAX_WITH = null;
    public const ?int FILE_USER_IMAGE_MIN_HEIGH = null;
    public const ?int FILE_USER_IMAGE_MAX_HEIGH = null;
    public const ?int FILE_USER_IMAGE_MIN_PIXELS = null;
    public const ?int FILE_USER_IMAGE_MAX_PIXELS = null;
    public const ?float FILE_USER_IMAGE_MIN_ASPECT_RATIO = null;
    public const ?float FILE_USER_IMAGE_MAX_ASPECT_RATIO = null;
    public const bool FILE_USER_IMAGE_ALLOW_LANDSCAPE = true;
    public const bool FILE_USER_IMAGE_ALLOW_PORTRAIT = true;
    public const bool FILE_USER_IMAGE_ALLOW_SQUARE_IMAGE = true;
    public const bool FILE_USER_IMAGE_DETECT_CORRUPTED = false;
    public const array FILE_USER_IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
    ];

    /**
     * Constraints for group image.
     */

    /**
     * File size in Bytes.
     */
    public const int FILE_GROUP_IMAGE_MAX_FILE_SIZE = 2 * 1_000_000;
    public const ?int FILE_GROUP_IMAGE_NAME_MAX_LENGTH = null;
    public const ?int FILE_GROUP_IMAGE_MIN_WITH = null;
    public const ?int FILE_GROUP_IMAGE_MAX_WITH = null;
    public const ?int FILE_GROUP_IMAGE_MIN_HEIGH = null;
    public const ?int FILE_GROUP_IMAGE_MAX_HEIGH = null;
    public const ?int FILE_GROUP_IMAGE_MIN_PIXELS = null;
    public const ?int FILE_GROUP_IMAGE_MAX_PIXELS = null;
    public const ?float FILE_GROUP_IMAGE_MIN_ASPECT_RATIO = null;
    public const ?float FILE_GROUP_IMAGE_MAX_ASPECT_RATIO = null;
    public const bool FILE_GROUP_IMAGE_ALLOW_LANDSCAPE = true;
    public const bool FILE_GROUP_IMAGE_ALLOW_PORTRAIT = true;
    public const bool FILE_GROUP_IMAGE_ALLOW_SQUARE_IMAGE = true;
    public const bool FILE_GROUP_IMAGE_DETECT_CORRUPTED = false;
    public const array FILE_GROUP_IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
    ];

    public const array GROUP_TYPE_VALUES = [
        GROUP_TYPE::GROUP,
        GROUP_TYPE::USER,
    ];
    public const array GROUP_ROLES = [
        GROUP_ROLES::ADMIN,
        GROUP_ROLES::USER,
    ];

    public const int DESCRIPTION_MAX_LENGTH = 500;
    public const string DESCRIPTION_TYPE = 'string';

    public const int ADDRESS_MIN_LENGTH = 0;
    public const int ADDRESS_MAX_LENGTH = 100;
    public const string ADDRESS_TYPE = 'string';

    public const array NOTIFICATION_TYPES = [
        NOTIFICATION_TYPE::USER_REGISTERED,
        NOTIFICATION_TYPE::USER_EMAIL_CHANGED,
        NOTIFICATION_TYPE::USER_PASSWORD_CHANGED,
        NOTIFICATION_TYPE::USER_PASSWORD_REMEMBER,
        NOTIFICATION_TYPE::GROUP_CREATED,
        NOTIFICATION_TYPE::GROUP_REMOVED,
        NOTIFICATION_TYPE::GROUP_USER_ADDED,
        NOTIFICATION_TYPE::GROUP_USER_REMOVED,
        NOTIFICATION_TYPE::GROUP_USER_SET_AS_ADMIN,
    ];

    public const array UNIT_MEASURE_TYPES = [
        UNIT_MEASURE_TYPE::UNITS,
        UNIT_MEASURE_TYPE::KG,
        UNIT_MEASURE_TYPE::CG,
        UNIT_MEASURE_TYPE::G,
        UNIT_MEASURE_TYPE::M,
        UNIT_MEASURE_TYPE::DM,
        UNIT_MEASURE_TYPE::CM,
        UNIT_MEASURE_TYPE::MM,
        UNIT_MEASURE_TYPE::L,
        UNIT_MEASURE_TYPE::DL,
        UNIT_MEASURE_TYPE::CL,
        UNIT_MEASURE_TYPE::ML,
    ];

    /**
     * File size in Bytes.
     */
    public const int FILE_PRODUCT_IMAGE_MAX_FILE_SIZE = 2 * 1_000_000;
    public const ?int FILE_PRODUCT_IMAGE_NAME_MAX_LENGTH = null;
    public const ?int FILE_PRODUCT_IMAGE_MIN_WITH = null;
    public const ?int FILE_PRODUCT_IMAGE_MAX_WITH = null;
    public const ?int FILE_PRODUCT_IMAGE_MIN_HEIGH = null;
    public const ?int FILE_PRODUCT_IMAGE_MAX_HEIGH = null;
    public const ?int FILE_PRODUCT_IMAGE_MIN_PIXELS = null;
    public const ?int FILE_PRODUCT_IMAGE_MAX_PIXELS = null;
    public const ?float FILE_PRODUCT_IMAGE_MIN_ASPECT_RATIO = null;
    public const ?float FILE_PRODUCT_IMAGE_MAX_ASPECT_RATIO = null;
    public const bool FILE_PRODUCT_IMAGE_ALLOW_LANDSCAPE = true;
    public const bool FILE_PRODUCT_IMAGE_ALLOW_PORTRAIT = true;
    public const bool FILE_PRODUCT_IMAGE_ALLOW_SQUARE_IMAGE = true;
    public const bool FILE_PRODUCT_IMAGE_DETECT_CORRUPTED = false;
    public const array FILE_PRODUCT_IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
    ];

    /**
     * File size in Bytes.
     */
    public const int FILE_SHOP_IMAGE_MAX_FILE_SIZE = 2 * 1_000_000;
    public const ?int FILE_SHOP_IMAGE_NAME_MAX_LENGTH = null;
    public const ?int FILE_SHOP_IMAGE_MIN_WITH = null;
    public const ?int FILE_SHOP_IMAGE_MAX_WITH = null;
    public const ?int FILE_SHOP_IMAGE_MIN_HEIGH = null;
    public const ?int FILE_SHOP_IMAGE_MAX_HEIGH = null;
    public const ?int FILE_SHOP_IMAGE_MIN_PIXELS = null;
    public const ?int FILE_SHOP_IMAGE_MAX_PIXELS = null;
    public const ?float FILE_SHOP_IMAGE_MIN_ASPECT_RATIO = null;
    public const ?float FILE_SHOP_IMAGE_MAX_ASPECT_RATIO = null;
    public const bool FILE_SHOP_IMAGE_ALLOW_LANDSCAPE = true;
    public const bool FILE_SHOP_IMAGE_ALLOW_PORTRAIT = true;
    public const bool FILE_SHOP_IMAGE_ALLOW_SQUARE_IMAGE = true;
    public const bool FILE_SHOP_IMAGE_DETECT_CORRUPTED = false;
    public const array FILE_SHOP_IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
    ];

    public const array FILTER_STRING_COMPARISON_TYPES = [
        FILTER_STRING_COMPARISON::STARTS_WITH,
        FILTER_STRING_COMPARISON::ENDS_WITH,
        FILTER_STRING_COMPARISON::CONTAINS,
        FILTER_STRING_COMPARISON::EQUALS,
    ];

    public const array FILTER_SECTIONS = [
        FILTER_SECTION::LIST_ORDERS,
        FILTER_SECTION::PRODUCT,
        FILTER_SECTION::SHOP,
        FILTER_SECTION::ORDER,
        FILTER_SECTION::GROUP,
        FILTER_SECTION::GROUP_USERS,
    ];
}
