<?php

declare(strict_types=1);

namespace Product\Application\SetProductShopPrice\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;
use Product\Domain\Model\ProductShop;

class SetProductShopPriceOutputDto implements ApplicationOutputInterface
{
    /**
     * @param ProductShop[] $productShop
     */
    public function __construct(
        private Identifier $groupId,
        private array $productShop
    ) {
    }

    #[\Override]
    public function toArray(): array
    {
        return array_map(
            fn (ProductShop $productShop) => [
                'group_id' => $this->groupId->getValue(),
                'product_id' => $productShop->getProductId()->getValue(),
                'shop_id' => $productShop->getShopId()->getValue(),
                'price' => $productShop->getPrice()->getValue(),
                'unit' => $productShop->getUnit()->getValue()->value,
            ],
            $this->productShop
        );
    }
}
