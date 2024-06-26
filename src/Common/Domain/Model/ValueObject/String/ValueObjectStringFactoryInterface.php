<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

interface ValueObjectStringFactoryInterface
{
    public static function createEmail(?string $email): Email;

    public static function createIdentifier(?string $id): Identifier;

    public static function createIdentifierNullAble(?string $id): IdentifierNullable;

    public static function createName(?string $name): Name;

    public static function createNameWithSpaces(?string $name): NameWithSpaces;

    public static function createAddress(?string $address): Address;

    public static function createDescription(?string $description): Description;

    public static function createPassword(?string $password): Password;

    public static function createPath(?string $path): Path;

    public static function createJwtToken(?string $path): JwtToken;

    public static function createUrl(?string $url): Url;

    public static function createLanguage(?string $lang): Language;
}
