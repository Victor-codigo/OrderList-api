<?php

declare(strict_types=1);

namespace Common\Domain\Service\Image\UploadImage;

class BuiltInFunctionsReturn
{
    public static ?bool $file_exists = null;
    public static ?bool $unlink = null;
}

function file_exists(string $filename): bool
{
    return BuiltInFunctionsReturn::$file_exists ?? \file_exists($filename);
}

function unlink(string $filename): bool
{
    return BuiltInFunctionsReturn::$unlink ?? \unlink($filename);
}
