<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

interface ValueObjectStringFactoryInterface
{
    public static function createEmail(string|null $email): Email;

    public static function createIdentifier(string|null $id): Identifier;

    public static function createIdentifierNullAble(string|null $id): IdentifierNullable;

    public static function createName(string|null $name): Name;

    public static function createNameWithSpaces(string|null $name): NameWithSpaces;

    public static function createDescription(string|null $description): Description;

    public static function createPassword(string|null $password): Password;

    public static function createPath(string|null $path): Path;

    public static function createJwtToken(string|null $path): JwtToken;

    public static function createUrl(string|null $url): Url;

    public static function createLanguage(string|null $lang): Language;
}
