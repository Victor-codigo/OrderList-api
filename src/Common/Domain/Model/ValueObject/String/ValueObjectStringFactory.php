<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

class ValueObjectStringFactory
{
    public static function createEmail(?string $email): Email
    {
        return new Email($email);
    }

    public static function createIdentifier(?string $id): Identifier
    {
        return new Identifier($id);
    }

    public static function createIdentifierNullAble(?string $id): IdentifierNullable
    {
        return new IdentifierNullable($id);
    }

    public static function createName(?string $name): Name
    {
        return new Name($name);
    }

    public static function createNameWithSpaces(?string $name): NameWithSpaces
    {
        return new NameWithSpaces($name);
    }

    public static function createAddress(?string $address): Address
    {
        return new Address($address);
    }

    public static function createDescription(?string $description): Description
    {
        return new Description($description);
    }

    public static function createPassword(?string $password): Password
    {
        return new Password($password);
    }

    public static function createPath(?string $path): Path
    {
        return new Path($path);
    }

    public static function createJwtToken(?string $path): JwtToken
    {
        return new JwtToken($path);
    }

    public static function createUrl(?string $url): Url
    {
        return new Url($url);
    }

    public static function createLanguage(?string $lang): Language
    {
        return new Language($lang);
    }
}
