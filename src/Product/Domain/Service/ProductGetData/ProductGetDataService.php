<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductGetData;

use Common\Domain\Ports\Paginator\PaginatorInterface;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductGetData\Dto\ProductGetDataDto;

class ProductGetDataService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(ProductGetDataDto $input): array
    {
        $products = $this->productRepository->findProductsOrFail(
            empty($input->productsId) ? null : $input->productsId,
            $input->groupId->isNull() ? null : $input->groupId,
            empty($input->shopsId) ? null : $input->shopsId,
            empty($input->productNameStartsWith) ? null : $input->productNameStartsWith
        );

        return $this->getProductsData($products, $input->productsMaxNumber);
    }

    private function getProductsData(PaginatorInterface $products, int $productsMaxNumber): array
    {
        $products->setPagination(1, $productsMaxNumber);
        $productsData = [];

        /** @var Product $product */
        foreach ($products as $product) {
            $productsData[] = [
                'id' => $product->getId()->getValue(),
                'group_id' => $product->getGroupId()->getValue(),
                'name' => $product->getName()->getValue(),
                'description' => $product->getDescription()->getValue(),
                'image' => $product->getImage()->getValue(),
                'created_on' => $product->getCreatedOn()->format('Y-m-d H:i:s'),
            ];
        }

        return $productsData;
    }
}
