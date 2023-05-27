<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductGetShopPrice;

use Common\Domain\Ports\Paginator\PaginatorInterface;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;
use Product\Domain\Service\ProductGetShopPrice\Dto\ProductGetShopPriceDto;

class ProductGetShopPriceService
{
    public function __construct(
        private ProductShopRepositoryInterface $productShopRepository
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(ProductGetShopPriceDto $input): array
    {
        $productsShopsPaginator = $this->productShopRepository->findProductsAndShopsOrFail($input->productsId, $input->shopsId, $input->groupId);

        return $this->getProductsData($productsShopsPaginator);
    }

    private function getProductsData(PaginatorInterface $productsShopsPaginator): array
    {
        return array_map(
            fn (ProductShop $productShop) => [
                'product_id' => $productShop->getProductId()->getValue(),
                'shop_id' => $productShop->getShopId()->getValue(),
                'price' => $productShop->getPrice()->getValue(),
            ],
            iterator_to_array($productsShopsPaginator)
        );
    }
}
