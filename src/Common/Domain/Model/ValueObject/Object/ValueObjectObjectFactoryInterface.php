<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Common\Domain\Ports\FileUpload\FileInterface;
use User\Domain\Model\USER_ROLES;

interface ValueObjectObjectFactoryInterface
{
    public static function createRol(USER_ROLES|null $rol): Rol;

    public static function createFile(FileInterface|null $file): File;

    public static function createUserImage(FileInterface|null $file): UserImage;
}
