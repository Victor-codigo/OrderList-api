<?php

declare(strict_types=1);

namespace Common\Domain\Service\Image\UploadImage;

use Common\Domain\Model\ValueObject\String\Path;

interface EntityImageModifyInterface
{
    public function setImage(Path $image): self;

    public function getImage(): Path;
}
