<?php

declare(strict_types=1);

namespace Common\Adapter\Image;

class BuiltInFunctionsReturn
{
    public static array|false|null $getimagesize = null;
}

function getimagesize(string $filename, ?array &$imageInfo = []): array|false
{
    if (null === BuiltInFunctionsReturn::$getimagesize) {
        return \getimagesize($filename, $imageInfo);
    }

    return BuiltInFunctionsReturn::$getimagesize;
}
