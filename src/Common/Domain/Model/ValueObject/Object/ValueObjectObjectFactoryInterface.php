<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use App\Group\Domain\Model\GROUP_TYPE;
use Common\Domain\Ports\FileUpload\FileInterface;

interface ValueObjectObjectFactoryInterface
{
    public static function createRol(\BackedEnum|null $rol): Rol;

    public static function createFile(FileInterface|null $file): File;

    public static function createUserImage(FileInterface|null $file): UserImage;

    public static function createGroupType(GROUP_TYPE|null $type): GroupType;
}
