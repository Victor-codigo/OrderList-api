<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductModify;

class BuiltInFunctionsReturn
{
    public static bool|null $file_exists = null;
    public static bool|null $unlink = null;
}

function file_exists(string $filename): bool
{
    return BuiltInFunctionsReturn::$file_exists ?? \file_exists($filename);
}

function unlink(string $filename): bool
{
    return BuiltInFunctionsReturn::$unlink ?? \unlink($filename);
}
