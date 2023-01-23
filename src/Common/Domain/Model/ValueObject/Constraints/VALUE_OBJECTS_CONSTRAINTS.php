<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Constraints;

use Group\Domain\Model\GROUP_ROLES;
use Group\Domain\Model\GROUP_TYPE;
use User\Domain\Model\USER_ROLES;

final class VALUE_OBJECTS_CONSTRAINTS
{
    public const ID_LENGTH = 36;
    public const ID_TYPE = 'string';

    public const EMAIL_LENGTH = 50;
    public const EMAIL_TYPE = 'string';

    public const PASSWORD_MIN_LENGTH = 6;
    public const PASSWORD_MAX_LENGTH = 50;
    public const PASSWORD_TYPE = 'string';

    public const NAME_MIN_LENGTH = 1;
    public const NAME_MAX_LENGTH = 50;
    public const NAME_TYPE = 'string';

    public const ROLES_LENGTH = 50;
    public const ROLES_TYPE = 'json';
    public const ROLES_VALUES = [USER_ROLES::ADMIN, USER_ROLES::USER, USER_ROLES::NOT_ACTIVE];

    public const CREATED_ON_LENGTH = 50;
    public const CREATED_ON_TYPE = 'json';

    public const PATH_MIN_LENGTH = 1;
    public const PATH_MAX_LENGTH = 256;

    public const JWT_TOKEN_MIN_LENGTH = 36;

    /**
     * File size in Bytes.
     */
    public const FILE_MAX_FILE_SIZE = 2 * 1_000_000;
    public const FILE_MIME_TIPES = [];

    /**
     * File size in Bytes.
     */
    public const FILE_USER_IMAGE_MAX_FILE_SIZE = 2 * 1_000_000;
    public const FILE_USER_IMAGE_MIN_WITH = null;
    public const FILE_USER_IMAGE_MAX_WITH = null;
    public const FILE_USER_IMAGE_MIN_HEIGTH = null;
    public const FILE_USER_IMAGE_MAX_HEIGTH = null;
    public const FILE_USER_IMAGE_MIN_PIXELS = null;
    public const FILE_USER_IMAGE_MAX_PIXELS = null;
    public const FILE_USER_IMAGE_MIN_ASPECT_RATIO = null;
    public const FILE_USER_IMAGE_MAX_ASPECT_RATIO = null;
    public const FILE_USER_IMAGE_ALLOW_LANDSCAPE = true;
    public const FILE_USER_IMAGE_ALLOW_PORTRAIT = true;
    public const FILE_USER_IMAGE_ALLOW_SQUARE_IMAGE = true;
    public const FILE_USER_IMAGE_DETECT_CORRUPTED = false;
    public const FILE_USER_IMAGE_MIME_TIPES = [
        'image/jpeg',
        'image/png',
        'image/bmp',
    ];

    public const GROUP_TYPE_VALUES = [
        GROUP_TYPE::GROUP,
        GROUP_TYPE::USER,
    ];
    public const GROUP_ROLES = [
        GROUP_ROLES::ADMIN,
        GROUP_ROLES::USER,
    ];

    public const DESCRIPTION_MAX_LENGTH = 500;
    public const DESCRIPTION_TYPE = 'string';
}
