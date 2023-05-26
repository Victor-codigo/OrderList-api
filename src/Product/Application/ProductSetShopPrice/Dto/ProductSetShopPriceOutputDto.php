<?php

declare(strict_types=1);

namespace Product\Application\ProductSetShopPrice\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Product\Domain\Model\ProductShop;

class ProductSetShopPriceOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        private ProductShop $productShop
    ) {
    }

    public function toArray(): array
    {
        return [
            'product_id' => $this->productShop->getProductId()->getValue(),
            'shop_id' => $this->productShop->getShopId()->getValue(),
            'price' => $this->productShop->getPrice()->getValue(),
        ];
    }
}
