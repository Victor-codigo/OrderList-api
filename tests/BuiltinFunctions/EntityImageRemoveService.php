<?php

declare(strict_types=1);

namespace Common\Domain\Service\Image\EntityImageRemove;

class BuiltInFunctionsReturn
{
    public static bool|null $file_exists;
    public static bool|null $unlink;
}

function file_exists(string $filename): bool
{
    return BuiltInFunctionsReturn::$file_exists ?? \file_exists($filename);
}

function unlink(string $filename): bool
{
    return BuiltInFunctionsReturn::$unlink ?? \unlink($filename);
}
