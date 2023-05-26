<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductSetShopPrice\Dto;

use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\String\Identifier;

class ProductSetShopPriceDto
{
    public function __construct(
        public readonly Identifier $productId,
        public readonly Identifier $shopId,
        public readonly Identifier $groupId,
        public readonly Money $price
    ) {
    }
}
