<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject;

use Common\Domain\Model\ValueObject\Array\NotificationData;
use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\Array\ValueObjectArrayFactoryInterface;
use Common\Domain\Model\ValueObject\Array\valueObjectArrayFactory;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\Integer\ValueObjectIntegerFactory;
use Common\Domain\Model\ValueObject\Object\File;
use Common\Domain\Model\ValueObject\Object\GroupImage;
use Common\Domain\Model\ValueObject\Object\GroupType;
use Common\Domain\Model\ValueObject\Object\NotificationType;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\Object\UserImage;
use Common\Domain\Model\ValueObject\Object\ValueObjectObjectFactory;
use Common\Domain\Model\ValueObject\Object\ValueObjectObjectFactoryInterface;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\JwtToken;
use Common\Domain\Model\ValueObject\String\Language;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\String\Url;
use Common\Domain\Model\ValueObject\String\ValueObjectStringFactory;
use Common\Domain\Model\ValueObject\String\ValueObjectStringFactoryInterface;
use Common\Domain\Ports\FileUpload\FileInterface;
use Group\Domain\Model\GROUP_TYPE;
use Notification\Domain\Model\NOTIFICATION_TYPE;

final class ValueObjectFactory implements ValueObjectStringFactoryInterface, ValueObjectArrayFactoryInterface, ValueObjectObjectFactoryInterface
{
    /**
     * @param Rol[]|null $roles
     */
    public static function createRoles(array|null $roles): Roles
    {
        return valueObjectArrayFactory::createRoles($roles);
    }

    public static function createRol(\BackedEnum|null $roles): Rol
    {
        return ValueObjectObjectFactory::createRol($roles);
    }

    public static function createNotificationData(array|null $data): NotificationData
    {
        return valueObjectArrayFactory::createNotificationData($data);
    }

    public static function createEmail(string|null $email): Email
    {
        return ValueObjectStringFactory::createEmail($email);
    }

    public static function createIdentifier(string|null $id): Identifier
    {
        return ValueObjectStringFactory::createIdentifier($id);
    }

    public static function createName(string|null $name): Name
    {
        return ValueObjectStringFactory::createName($name);
    }

    public static function createDescription(string|null $description): Description
    {
        return ValueObjectStringFactory::createDescription($description);
    }

    public static function createPassword(string|null $password): Password
    {
        return ValueObjectStringFactory::createPassword($password);
    }

    public static function createPath(string|null $path): Path
    {
        return ValueObjectStringFactory::createPath($path);
    }

    public static function createJwtToken(string|null $path): JwtToken
    {
        return ValueObjectStringFactory::createJwtToken($path);
    }

    public static function createUrl(string|null $url): Url
    {
        return ValueObjectStringFactory::createUrl($url);
    }

    public static function createLanguage(string|null $language): Language
    {
        return ValueObjectStringFactory::createLanguage($language);
    }

    public static function createFile(FileInterface|null $file): File
    {
        return ValueObjectObjectFactory::createFile($file);
    }

    public static function createUserImage(FileInterface|null $file): UserImage
    {
        return ValueObjectObjectFactory::createUserImage($file);
    }

    public static function createGroupImage(FileInterface|null $file): GroupImage
    {
        return ValueObjectObjectFactory::createGroupImage($file);
    }

    public static function createGroupType(GROUP_TYPE|null $type): GroupType
    {
        return ValueObjectObjectFactory::createGroupType($type);
    }

    public static function createPaginatorPage(int|null $page): PaginatorPage
    {
        return ValueObjectIntegerFactory::createPaginatorPage($page);
    }

    public static function createPaginatorPageItems(int|null $pageItems): PaginatorPageItems
    {
        return ValueObjectIntegerFactory::createPaginatorPageItems($pageItems);
    }

    public static function createNotificationType(NOTIFICATION_TYPE|null $type): NotificationType
    {
        return ValueObjectObjectFactory::createNotificationType($type);
    }
}
