<?php

declare(strict_types=1);

namespace Common\Domain\Service\Image\UploadImage\Dto;

use Common\Domain\Model\ValueObject\Object\ObjectValueObject;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Service\Image\EntityImageModifyInterface;

class UploadImageDto
{
    /**
     * @param EntityImageModifyInterface $entity            Entity to set the image
     * @param Path                       $imagesPathToStore relative path to images dir
     * @param ObjectValueObject|null     $imageUploaded     Image to save
     */
    public function __construct(
        public readonly EntityImageModifyInterface $entity,
        public readonly Path $imagesPathToStore,
        public readonly ?ObjectValueObject $imageUploaded,
        public readonly bool $remove,
        public readonly ?float $resizeWidth,
        public readonly ?float $resizeHeight
    ) {
    }
}
