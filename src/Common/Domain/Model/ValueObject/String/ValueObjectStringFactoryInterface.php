<?php

declare(strict_types=1);

namespace Common\Domain\Model\ValueObject\String;

interface ValueObjectStringFactoryInterface
{
    public static function createEmail(string|null $email): Email;

    public static function createIdentifier(string|null $id): Identifier;

    public static function createName(string|null $name): Name;

    public static function createPassword(string|null $password): Password;

    public static function createPath(string|null $path): Path;
}
