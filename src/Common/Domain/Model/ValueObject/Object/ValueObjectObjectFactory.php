<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Common\Domain\Ports\FileUpload\FileInterface;
use Group\Domain\Model\GROUP_TYPE;

class ValueObjectObjectFactory
{
    public static function createRol(\BackedEnum|null $rol): Rol
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

    public static function createGroupType(GROUP_TYPE|null $type): GroupType
    {
        return new GroupType($type);
    }
}
