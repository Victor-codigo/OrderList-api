<?php

declare(strict_types=1);

namespace Product\Domain\Service\SetProductShopPrice\Dto;

use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\Object\UnitMeasure;
use Common\Domain\Model\ValueObject\String\Identifier;

class SetProductShopPriceDto
{
    /**
     * @param Identifier[]  $productsOrShopsId
     * @param Money[]       $prices
     * @param UnitMeasure[] $units
     */
    public function __construct(
        public readonly Identifier $groupId,
        public readonly Identifier $productId,
        public readonly Identifier $shopId,
        public readonly array $productsOrShopsId,
        public readonly array $prices,
        public readonly array $units
    ) {
    }
}
