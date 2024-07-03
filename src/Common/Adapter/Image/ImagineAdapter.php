<?php

declare(strict_types=1);

namespace Common\Adapter\Image;

use Common\Domain\Image\Exception\ImageResizeException;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Ports\Image\ImageInterface;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

class ImagineAdapter implements ImageInterface
{
    public function __construct(
        private Imagine $imagine,
    ) {
    }

    /**
     * @throws ImageResizeException
     */
    #[\Override]
    public function resizeToAFrame(Path $filePath, float $widthMax, float $heightMax): void
    {
        if (0 == $widthMax || 0 == $heightMax) {
            throw ImageResizeException::fromMessage('Image width or height is 0');
        }

        ['width' => $imageWidth,'height' => $imageHeight] = $this->getImageSize($filePath);
        ['width' => $imageResizedWidth, 'height' => $imageResizedHeight] = $this->getImageWidthResized($imageWidth, $imageHeight, $widthMax);
        ['width' => $imageResizedWidth, 'height' => $imageResizedHeight] = $this->getImageHeightResized($imageResizedWidth, $imageResizedHeight, $heightMax);

        try {
            $this->imagine
               ->open($filePath->getValue())
               ->resize(new Box($imageResizedWidth, $imageResizedHeight))
               ->save($filePath->getValue());
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            throw ImageResizeException::fromMessage($e->getMessage());
        }
    }

    /**
     * @return array<{width: int, height: int}>
     *
     * @throws ImageResizeException
     */
    private function getImageSize(Path $file): array
    {
        $imageSize = getimagesize($file->getValue());

        if (false === $imageSize) {
            throw ImageResizeException::fromMessage('Error getting image size');
        }

        if (0 === $imageSize[0] || 0 === $imageSize[1]) {
            throw ImageResizeException::fromMessage('Image width or height is 0');
        }

        return [
            'width' => $imageSize[0],
            'height' => $imageSize[1],
        ];
    }

    /**
     * @return array<{width: int, height: int}>
     */
    private function getImageWidthResized(float $imageWidth, float $imageHeight, float $widthMax): array
    {
        $ratioWidth = $widthMax / $imageWidth;

        if ($ratioWidth >= 1) {
            return [
                'width' => $imageWidth,
                'height' => $imageHeight,
            ];
        }

        return [
            'width' => $imageWidth * $ratioWidth,
            'height' => $imageHeight * $ratioWidth,
        ];
    }

    /**
     * @return array<{width: int, height: int}>
     */
    private function getImageHeightResized(float $imageWidth, float $imageHeight, float $heightMax): array
    {
        $ratioHeight = $heightMax / $imageHeight;

        if ($ratioHeight >= 1) {
            return [
                'width' => $imageWidth,
                'height' => $imageHeight,
            ];
        }

        return [
            'width' => $imageWidth * $ratioHeight,
            'height' => $imageHeight * $ratioHeight,
        ];
    }
}
