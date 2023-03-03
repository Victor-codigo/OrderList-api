<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupModify;

class BuiltInFunctionsReturn
{
    public static $unlink = null;
    public static $file_exists = null;
}

function unlink(string $fileName): bool
{
    return BuiltInFunctionsReturn::$unlink ?? \unlink($fileName);
}

function file_exists(string $fileName): bool
{
    return BuiltInFunctionsReturn::$file_exists ?? \file_exists($fileName);
}
