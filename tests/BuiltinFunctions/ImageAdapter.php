<?php

declare(strict_types=1);

namespace Common\Adapter\Image;

class BuiltInFunctionsReturn
{
    /**
     * @var array<int, int>|false|null
     */
    public static array|false|null $getimagesize = null;
}

/**
 * @param int[] $imageInfo
 *
 * @return array{0: int, 1: int, 2: int, 3: string, bits: int, channels: int, mime: string}|false
 */
function getimagesize(string $filename, ?array &$imageInfo = []): array|false
{
    if (null === BuiltInFunctionsReturn::$getimagesize) {
        return \getimagesize($filename, $imageInfo);
    }

    return BuiltInFunctionsReturn::$getimagesize;
}
