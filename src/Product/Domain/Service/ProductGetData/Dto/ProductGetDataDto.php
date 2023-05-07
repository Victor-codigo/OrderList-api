<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductGetData\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ProductGetDataDto
{
    /**
     * @param Identifier[] $productId
     * @param Identifier[] $shops
     */
    public function __construct(
        public readonly Identifier $groupId,
        public readonly array $productsId,
        public readonly array $shopsId,
        public readonly string|null $productNameStartsWith,
        public readonly int $productsMaxNumber = 100
    ) {
    }
}
