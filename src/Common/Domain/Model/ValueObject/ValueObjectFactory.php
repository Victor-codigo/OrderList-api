<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject;

use Common\Domain\Model\ValueObject\String\Email;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Model\ValueObject\String\Password;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Model\ValueObject\String\ValueObjectStringFactory;
use Common\Domain\Model\ValueObject\String\ValueObjectStringFactoryInterface;
use Common\Domain\Model\ValueObject\array\Roles;
use Common\Domain\Model\ValueObject\array\ValueObjectArrayFactoryInterface;
use Common\Domain\Model\ValueObject\array\valueObjectArrayFactory;

final class ValueObjectFactory implements ValueObjectStringFactoryInterface, ValueObjectArrayFactoryInterface
{
    public static function createRoles(array|null $roles): Roles
    {
        return valueObjectArrayFactory::createRoles($roles);
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

    public static function createPassword(string|null $password): Password
    {
        return ValueObjectStringFactory::createPassword($password);
    }

    public static function createPath(string|null $path): Path
    {
        return ValueObjectStringFactory::createPath($path);
    }
}