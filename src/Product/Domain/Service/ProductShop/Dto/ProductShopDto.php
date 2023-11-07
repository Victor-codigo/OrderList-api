<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductShop\Dto;

use Common\Domain\Model\ValueObject\Float\Money;
use Product\Domain\Model\Product;
use Shop\Domain\Model\Shop;

class ProductShopDto
{
    public function __construct(
        public readonly Product $product,
        public readonly Shop $shop,
        public readonly Money $price,
        public readonly bool $remove = false,
    ) {
    }
}
