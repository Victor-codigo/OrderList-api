<?php

declare(strict_types=1);

namespace Product\Domain\Service\GetProductShopPrice\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class GetProductShopPriceDto
{
    /**
     * @param Identifier[] $productsId
     * @param Identifier[] $shopsId
     */
    public function __construct(
        public readonly array $productsId,
        public readonly array $shopsId,
        public readonly Identifier $groupId
    ) {
    }
}
