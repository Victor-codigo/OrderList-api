<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject;

use App\Group\Domain\Model\GROUP_TYPE;
use Common\Domain\Model\ValueObject\Array\Roles;
use Common\Domain\Model\ValueObject\Array\ValueObjectArrayFactoryInterface;
use Common\Domain\Model\ValueObject\Array\valueObjectArrayFactory;
use Common\Domain\Model\ValueObject\Object\File;
use Common\Domain\Model\ValueObject\Object\GroupType;
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

    public static function createGroupType(GROUP_TYPE|null $type): GroupType
    {
        return ValueObjectObjectFactory::createGroupType($type);
    }
}
