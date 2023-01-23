<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Common\Domain\Ports\FileUpload\FileInterface;
use Group\Domain\Model\GROUP_TYPE;

interface ValueObjectObjectFactoryInterface
{
    public static function createRol(\BackedEnum|null $rol): Rol;

    public static function createFile(FileInterface|null $file): File;

    public static function createUserImage(FileInterface|null $file): UserImage;

    public static function createGroupType(GROUP_TYPE|null $type): GroupType;
}
