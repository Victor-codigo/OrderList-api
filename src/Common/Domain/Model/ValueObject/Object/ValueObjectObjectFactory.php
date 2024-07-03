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

class ValueObjectObjectFactory
{
    public static function createRol(?\BackedEnum $rol): Rol
    {
        return new Rol($rol);
    }

    public static function createFile(?FileInterface $file): File
    {
        return new File($file);
    }

    public static function createUserImage(?FileInterface $file): UserImage
    {
        return new UserImage($file);
    }

    public static function createGroupImage(?FileInterface $file): GroupImage
    {
        return new GroupImage($file);
    }

    public static function createGroupType(?GROUP_TYPE $type): GroupType
    {
        return new GroupType($type);
    }

    public static function createNotificationType(?NOTIFICATION_TYPE $type): NotificationType
    {
        return new NotificationType($type);
    }

    public static function createUnit(?UNIT_MEASURE_TYPE $type): UnitMeasure
    {
        return new UnitMeasure($type);
    }

    public static function createProductImage(?FileInterface $file): ProductImage
    {
        return new ProductImage($file);
    }

    public static function createShopImage(?FileInterface $file): ShopImage
    {
        return new ShopImage($file);
    }

    public static function createFilterDbLikeComparison(?\BackedEnum $filter): FilterDbLikeComparison
    {
        return new FilterDbLikeComparison($filter);
    }

    public static function createFilterSection(?FILTER_SECTION $filter): FilterSection
    {
        return new FilterSection($filter);
    }
}
