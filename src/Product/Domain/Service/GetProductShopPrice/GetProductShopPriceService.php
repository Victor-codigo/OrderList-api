<?php

declare(strict_types=1);

namespace Product\Domain\Service\GetProductShopPrice;

use Common\Domain\Ports\Paginator\PaginatorInterface;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;
use Product\Domain\Service\GetProductShopPrice\Dto\GetProductShopPriceDto;

class GetProductShopPriceService
{
    public function __construct(
        private ProductShopRepositoryInterface $productShopRepository,
    ) {
    }

    /**
     * @return array<int, array{
     *  product_id: string|null,
     *  shop_id: string|null,
     *  price: float|null,
     *  unit: string|null
     * }>
     *
     * @throws DBNotFoundException
     */
    public function __invoke(GetProductShopPriceDto $input): array
    {
        $productsShopsPaginator = $this->productShopRepository->findProductsAndShopsOrFail($input->productsId, $input->shopsId, $input->groupId);

        return $this->getProductsData($productsShopsPaginator);
    }

    /**
     * @param PaginatorInterface<int, ProductShop> $productsShopsPaginator
     *
     * @return array<int, array{
     *  product_id: string|null,
     *  shop_id: string|null,
     *  price: float|null,
     *  unit: string|null
     * }>
     */
    private function getProductsData(PaginatorInterface $productsShopsPaginator): array
    {
        return array_map(
            fn (ProductShop $productShop): array => [
                'product_id' => $productShop->getProductId()->getValue(),
                'shop_id' => $productShop->getShopId()->getValue(),
                'price' => $productShop->getPrice()->getValue(),
                'unit' => $productShop->getUnit()->getValue()->value,
            ],
            iterator_to_array($productsShopsPaginator)
        );
    }
}
