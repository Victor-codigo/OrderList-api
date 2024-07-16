<?php

declare(strict_types=1);

namespace User\Application\UserModify;

class BuiltInFunctionsReturn
{
    public static ?bool $unlink = null;
    public static ?bool $is_readable = null;
    public static ?bool $file_exists = null;
}

function is_readable(string $path): bool
{
    return BuiltInFunctionsReturn::$is_readable ?? \is_readable($path);
}

function unlink(string $fileName): bool
{
    return BuiltInFunctionsReturn::$unlink ?? \unlink($fileName);
}

function file_exists(string $fileName): bool
{
    return BuiltInFunctionsReturn::$file_exists ?? \file_exists($fileName);
}
