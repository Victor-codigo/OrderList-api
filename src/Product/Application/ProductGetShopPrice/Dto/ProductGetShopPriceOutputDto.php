<?php

declare(strict_types=1);

namespace Product\Application\ProductGetShopPrice\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

class ProductGetShopPriceOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        private array $productsShops
    ) {
    }

    public function toArray(): array
    {
        return $this->productsShops;
    }
}
