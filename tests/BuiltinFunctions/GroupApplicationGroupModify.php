<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupModify;

class BuiltInFunctionsReturn
{
    public static ?\Closure $unlink = null;
    public static ?\Closure $file_exists = null;
}

function unlink(string $fileName): bool
{
    return null === BuiltInFunctionsReturn::$unlink ?: (\Closure::fromCallable(BuiltInFunctionsReturn::$unlink))($fileName);
}

function file_exists(string $fileName): ?bool
{
    return null === BuiltInFunctionsReturn::$file_exists ?: (\Closure::fromCallable(BuiltInFunctionsReturn::$file_exists))($fileName);
}
