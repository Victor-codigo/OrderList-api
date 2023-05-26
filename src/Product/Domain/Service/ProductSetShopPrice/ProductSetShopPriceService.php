<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductSetShopPrice;

use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;
use Product\Domain\Service\ProductSetShopPrice\Dto\ProductSetShopPriceDto;

class ProductSetShopPriceService
{
    public function __construct(
        private ProductShopRepositoryInterface $productShopRepository,
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(ProductSetShopPriceDto $input): ProductShop
    {
        /** @var ProductShop $productShop */
        $productShop = iterator_to_array(
            $this->productShopRepository->findProductsAndShopsOrFail($input->productId, $input->shopId, $input->groupId)
        )[0];

        $productShop->setPrice($input->price);
        $this->productShopRepository->save($productShop);

        return $productShop;
    }
}
