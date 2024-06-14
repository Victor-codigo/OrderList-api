<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductRemoveAllGroupsProducts;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Service\Image\EntityImageRemove\EntityImageRemoveService;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Service\ProductRemoveAllGroupsProducts\Dto\ProductRemoveAllGroupsProductsDto;

class ProductRemoveAllGroupsProductsService
{
    private const SHOP_PAGINATION_PAGE_ITEMS = 100;

    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private EntityImageRemoveService $entityImageRemoveService,
        private string $productImagePath
    ) {
    }

    /**
     * @return Identifier[]
     *
     * @throws DBNotFoundException
     * @throws DBConnectionException
     * @throws DomainInternalErrorException
     */
    public function __invoke(ProductRemoveAllGroupsProductsDto $input): array
    {
        $productsPaginator = $this->productRepository->findGroupsProductsOrFail($input->groupsId);

        $productsId = [];
        foreach ($productsPaginator->getAllPages(self::SHOP_PAGINATION_PAGE_ITEMS) as $productsIterator) {
            $products = iterator_to_array($productsIterator);
            $productsId[] = array_map(
                fn (Product $product) => $product->getId(),
                $products
            );

            $this->removeShopsImages($products);
            $this->productRepository->remove($products);
        }

        return array_merge(...$productsId);
    }

    /**
     * @param Product[] $products
     *
     * @throws DomainInternalErrorException
     */
    private function removeShopsImages(array $products): void
    {
        $shopImagePath = ValueObjectFactory::createPath($this->productImagePath);

        foreach ($products as $product) {
            $this->entityImageRemoveService->__invoke($product, $shopImagePath);
        }
    }
}
