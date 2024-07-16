<?php

declare(strict_types=1);

namespace Symfony\Component\Validator\Constraints;

class BuiltInFunctionsReturn
{
    public static ?bool $is_readable = null;
    public static ?int $filesize = null;
    public static ?array $getimagesize = null;
    public static ?bool $unlink = null;
    public static \GdImage|false|null $imagecreatefromstring = null;
}

function is_readable(string $path): bool
{
    return BuiltInFunctionsReturn::$is_readable ?? \is_readable($path);
}

function filesize(string $fileName): int
{
    return BuiltInFunctionsReturn::$filesize ?? \filesize($fileName);
}

function getimagesize(string $filename, ?array &$imageInfo = null): array
{
    return BuiltInFunctionsReturn::$getimagesize ?? \getimagesize($filename, $imageInfo);
}

function imagecreatefromstring(string $data): \GdImage|false
{
    return BuiltInFunctionsReturn::$imagecreatefromstring ?? \imagecreatefromstring($data);
}
