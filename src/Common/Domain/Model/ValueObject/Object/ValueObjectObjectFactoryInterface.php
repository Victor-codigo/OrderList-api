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
    public static function createRol(?\BackedEnum $rol): Rol;

    public static function createFile(?FileInterface $file): File;

    public static function createUserImage(?FileInterface $file): UserImage;

    public static function createGroupImage(?FileInterface $file): GroupImage;

    public static function createGroupType(?GROUP_TYPE $type): GroupType;

    public static function createNotificationType(?NOTIFICATION_TYPE $type): NotificationType;

    public static function createUnit(?UNIT_MEASURE_TYPE $type): UnitMeasure;

    public static function createProductImage(?FileInterface $file): ProductImage;

    public static function createShopImage(?FileInterface $file): ShopImage;

    public static function createFilterDbLikeComparison(?\BackedEnum $filter): FilterDbLikeComparison;

    public static function createFilterSection(?FILTER_SECTION $filter): FilterSection;
}
