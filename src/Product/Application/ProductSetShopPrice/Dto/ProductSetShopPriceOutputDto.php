<?php

declare(strict_types=1);

namespace Product\Application\ProductSetShopPrice\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;
use Product\Domain\Model\ProductShop;

class ProductSetShopPriceOutputDto implements ApplicationOutputInterface
{
    /**
     * @param ProductShop[] $productShop
     */
    public function __construct(
        private Identifier $groupId,
        private array $productShop
    ) {
    }

    public function toArray(): array
    {
        return array_map(
            fn (ProductShop $productShop) => [
                'group_id' => $this->groupId->getValue(),
                'product_id' => $productShop->getProductId()->getValue(),
                'shop_id' => $productShop->getShopId()->getValue(),
                'price' => $productShop->getPrice()->getValue(),
            ],
            $this->productShop
        );
    }
}
