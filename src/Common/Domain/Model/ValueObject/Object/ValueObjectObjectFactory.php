<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Common\Domain\Ports\FileUpload\FileInterface;
use User\Domain\Model\USER_ROLES;

class ValueObjectObjectFactory
{
    public static function createRol(USER_ROLES|null $rol): Rol
    {
        return new Rol($rol);
    }

    public static function createFile(FileInterface|null $file): File
    {
        return new File($file);
    }

    public static function createUserImage(FileInterface|null $file): UserImage
    {
        return new UserImage($file);
    }
}
