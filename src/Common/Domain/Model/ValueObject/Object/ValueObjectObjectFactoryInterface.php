<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\Object;

use Common\Domain\Model\ValueObject\Object\Filter\FilterDbLikeComparison;
use Common\Domain\Model\ValueObject\Object\Filter\FilterSection;
use Common\Domain\Ports\FileUpload\FileInterface;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;

interface ValueObjectObjectFactoryInterface
{
    public static function createRol(\BackedEnum|null $rol): Rol;

    public static function createFile(FileInterface|null $file): File;

    public static function createUserImage(FileInterface|null $file): UserImage;

    public static function createGroupImage(FileInterface|null $file): GroupImage;

    public static function createGroupType(GROUP_TYPE|null $type): GroupType;

    public static function createNotificationType(NOTIFICATION_TYPE|null $type): NotificationType;

    public static function createUnit(UNIT_MEASURE_TYPE|null $type): UnitMeasure;

    public static function createProductImage(FileInterface|null $file): ProductImage;

    public static function createShopImage(FileInterface|null $file): ShopImage;

    public static function createFilterDbLikeComparison(\BackedEnum|null $filter): FilterDbLikeComparison;

    public static function createFilterSection(FILTER_SECTION|null $filter): FilterSection;
}
