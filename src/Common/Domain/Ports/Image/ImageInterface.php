<?php

declare(strict_types=1);

namespace Common\Domain\Ports\Image;

use Common\Domain\Model\ValueObject\String\Path;

interface ImageInterface
{
    /**
     * @throws ImageResizeException
     */
    public function resizeToAFrame(Path $filePath, float $widthMax, float $heightMax): void;
}
