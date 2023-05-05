<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductRemove;

class BuiltInFunctionsReturn
{
    public static bool $file_exists;
    public static bool $unlink;
}

function file_exists(string $filename): bool
{
    return BuiltInFunctionsReturn::$file_exists ?? \file_exists($filename);
}

function unlink(string $filename): bool
{
    return BuiltInFunctionsReturn::$unlink ?? \unlink($filename);
}
