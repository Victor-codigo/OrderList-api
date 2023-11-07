<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductModify\Dto;

use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\Object\ProductImage;
use Common\Domain\Model\ValueObject\String\Description;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;

class ProductModifyDto
{
    public function __construct(
        public readonly Identifier $productId,
        public readonly Identifier $groupId,
        public readonly Identifier $shopId,
        public readonly NameWithSpaces $name,
        public readonly Description $description,
        public readonly Money $price,
        public readonly ProductImage $image,
        public readonly bool $imageRemove
    ) {
    }
}
