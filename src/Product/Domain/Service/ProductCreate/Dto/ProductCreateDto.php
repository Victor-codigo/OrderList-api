<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductCreate\Dto;

use Common\Domain\Model\ValueObject\Object\ProductImage;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;

class ProductCreateDto
{
    public function __construct(
        public readonly Identifier $groupId,
        public readonly NameWithSpaces $name,
        public readonly Description $description,
        public readonly ProductImage $image
    ) {
    }
}
