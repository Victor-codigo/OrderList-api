<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductSetShopPrice\Dto;

use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\String\Identifier;

class ProductSetShopPriceDto
{
    /**
     * @param Identifier[] $productsId
     * @param Identifier[] $shopsId
     * @param Money[]      $prices
     */
    public function __construct(
        public readonly Identifier $groupId,
        public readonly array $productsId,
        public readonly array $shopsId,
        public readonly array $prices
    ) {
    }
}
