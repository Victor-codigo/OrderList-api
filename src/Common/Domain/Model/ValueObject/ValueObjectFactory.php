<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject;

use Common\Domain\Model\ValueObject\Array\NotificationData;
use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\Array\valueObjectArrayFactory;
use Common\Domain\Model\ValueObject\Array\ValueObjectArrayFactoryInterface;
use Common\Domain\Model\ValueObject\Date\DateNowToFuture;
use Common\Domain\Model\ValueObject\Date\ValueObjectDateFactory;
use Common\Domain\Model\ValueObject\Date\ValueObjectDateFactoryInterface;
use Common\Domain\Model\ValueObject\Float\Amount;
use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\Float\ValueObjectFloatFactory;
use Common\Domain\Model\ValueObject\Float\ValueObjectFloatFactoryInterface;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\Group\ValueObjectGroupFactory;
use Common\Domain\Model\ValueObject\Group\ValueObjectGroupFactoryInterface;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\Integer\ValueObjectIntegerFactory;
use Common\Domain\Model\ValueObject\Integer\ValueObjectIntegerFactoryInterface;
use Common\Domain\Model\ValueObject\Object\File;
use Common\Domain\Model\ValueObject\Object\Filter\FilterDbLikeComparison;
use Common\Domain\Model\ValueObject\Object\Filter\FilterSection;
use Common\Domain\Model\ValueObject\Object\Filter\ValueObjectFilterInterface;
use Common\Domain\Model\ValueObject\Object\GroupImage;
use Common\Domain\Model\ValueObject\Object\GroupType;
use Common\Domain\Model\ValueObject\Object\NotificationType;
use Common\Domain\Model\ValueObject\Object\ProductImage;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\Object\ShopImage;
use Common\Domain\Model\ValueObject\Object\UnitMeasure;
use Common\Domain\Model\ValueObject\Object\UserImage;
use Common\Domain\Model\ValueObject\Object\ValueObjectObjectFactory;
use Common\Domain\Model\ValueObject\Object\ValueObjectObjectFactoryInterface;
use Common\Domain\Model\ValueObject\String\Address;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\IdentifierNullable;
use Common\Domain\Model\ValueObject\String\JwtToken;
use Common\Domain\Model\ValueObject\String\Language;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\String\Url;
use Common\Domain\Model\ValueObject\String\ValueObjectStringFactory;
use Common\Domain\Model\ValueObject\String\ValueObjectStringFactoryInterface;
use Common\Domain\Ports\FileUpload\FileInterface;
use Common\Domain\Validation\Filter\FILTER_SECTION;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Common\Domain\Validation\Notification\NOTIFICATION_TYPE;
use Common\Domain\Validation\UnitMeasure\UNIT_MEASURE_TYPE;

final class ValueObjectFactory implements ValueObjectStringFactoryInterface, ValueObjectArrayFactoryInterface, ValueObjectObjectFactoryInterface, ValueObjectIntegerFactoryInterface, ValueObjectFloatFactoryInterface, ValueObjectDateFactoryInterface, ValueObjectGroupFactoryInterface
{
    /**
     * @param Rol[]|null $roles
     */
    #[\Override]
    public static function createRoles(?array $roles): Roles
    {
        return valueObjectArrayFactory::createRoles($roles);
    }

    #[\Override]
    public static function createRol(?\BackedEnum $roles): Rol
    {
        return ValueObjectObjectFactory::createRol($roles);
    }

    #[\Override]
    public static function createNotificationData(?array $data): NotificationData
    {
        return valueObjectArrayFactory::createNotificationData($data);
    }

    #[\Override]
    public static function createEmail(?string $email): Email
    {
        return ValueObjectStringFactory::createEmail($email);
    }

    #[\Override]
    public static function createIdentifier(?string $id): Identifier
    {
        return ValueObjectStringFactory::createIdentifier($id);
    }

    #[\Override]
    public static function createIdentifierNullable(?string $id): IdentifierNullable
    {
        return ValueObjectStringFactory::createIdentifierNullAble($id);
    }

    #[\Override]
    public static function createName(?string $name): Name
    {
        return ValueObjectStringFactory::createName($name);
    }

    #[\Override]
    public static function createNameWithSpaces(?string $name): NameWithSpaces
    {
        return ValueObjectStringFactory::createNameWithSpaces($name);
    }

    #[\Override]
    public static function createAddress(?string $address): Address
    {
        return ValueObjectStringFactory::createAddress($address);
    }

    #[\Override]
    public static function createDescription(?string $description): Description
    {
        return ValueObjectStringFactory::createDescription($description);
    }

    #[\Override]
    public static function createPassword(?string $password): Password
    {
        return ValueObjectStringFactory::createPassword($password);
    }

    #[\Override]
    public static function createPath(?string $path): Path
    {
        return ValueObjectStringFactory::createPath($path);
    }

    #[\Override]
    public static function createJwtToken(?string $path): JwtToken
    {
        return ValueObjectStringFactory::createJwtToken($path);
    }

    #[\Override]
    public static function createUrl(?string $url): Url
    {
        return ValueObjectStringFactory::createUrl($url);
    }

    #[\Override]
    public static function createLanguage(?string $language): Language
    {
        return ValueObjectStringFactory::createLanguage($language);
    }

    #[\Override]
    public static function createFile(?FileInterface $file): File
    {
        return ValueObjectObjectFactory::createFile($file);
    }

    #[\Override]
    public static function createUserImage(?FileInterface $file): UserImage
    {
        return ValueObjectObjectFactory::createUserImage($file);
    }

    #[\Override]
    public static function createGroupImage(?FileInterface $file): GroupImage
    {
        return ValueObjectObjectFactory::createGroupImage($file);
    }

    #[\Override]
    public static function createGroupType(?GROUP_TYPE $type): GroupType
    {
        return ValueObjectObjectFactory::createGroupType($type);
    }

    #[\Override]
    public static function createPaginatorPage(?int $page): PaginatorPage
    {
        return ValueObjectIntegerFactory::createPaginatorPage($page);
    }

    #[\Override]
    public static function createPaginatorPageItems(?int $pageItems): PaginatorPageItems
    {
        return ValueObjectIntegerFactory::createPaginatorPageItems($pageItems);
    }

    #[\Override]
    public static function createMoney(?float $money): Money
    {
        return ValueObjectFloatFactory::createMoney($money);
    }

    #[\Override]
    public static function createAmount(?float $amount): Amount
    {
        return ValueObjectFloatFactory::createAmount($amount);
    }

    #[\Override]
    public static function createNotificationType(?NOTIFICATION_TYPE $type): NotificationType
    {
        return ValueObjectObjectFactory::createNotificationType($type);
    }

    #[\Override]
    public static function createUnit(?UNIT_MEASURE_TYPE $type): UnitMeasure
    {
        return ValueObjectObjectFactory::createUnit($type);
    }

    #[\Override]
    public static function createProductImage(?FileInterface $type): ProductImage
    {
        return ValueObjectObjectFactory::createProductImage($type);
    }

    #[\Override]
    public static function createShopImage(?FileInterface $type): ShopImage
    {
        return ValueObjectObjectFactory::createShopImage($type);
    }

    #[\Override]
    public static function createDateNowToFuture(?\DateTime $date): DateNowToFuture
    {
        return ValueObjectDateFactory::createDateNowToFuture($date);
    }

    #[\Override]
    public static function createFilterDbLikeComparison(?\BackedEnum $filter): FilterDbLikeComparison
    {
        return ValueObjectObjectFactory::createFilterDbLikeComparison($filter);
    }

    #[\Override]
    public static function createFilterSection(?FILTER_SECTION $filter): FilterSection
    {
        return ValueObjectObjectFactory::createFilterSection($filter);
    }

    #[\Override]
    public static function createFilter(string $id, ValueObjectBase&ValueObjectFilterInterface $type, ValueObjectBase $value): Filter
    {
        return ValueObjectGroupFactory::createFilter($id, $type, $value);
    }
}
