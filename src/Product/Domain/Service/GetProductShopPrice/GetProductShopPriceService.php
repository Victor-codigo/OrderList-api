<?php

declare(strict_types=1);

namespace Product\Domain\Service\GetProductShopPrice;

use Common\Domain\Ports\Paginator\PaginatorInterface;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;
use Product\Domain\Service\GetProductShopPrice\Dto\GetProductShopPriceDto;

class GetProductShopPriceService
{
    public function __construct(
        private ProductShopRepositoryInterface $productShopRepository
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(GetProductShopPriceDto $input): array
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
                'unit' => $productShop->getUnit()->getValue()->value,
            ],
            iterator_to_array($productsShopsPaginator)
        );
    }
}
