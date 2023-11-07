<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductShop;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\String\Identifier;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;
use Product\Domain\Service\ProductShop\Dto\ProductShopDto;
use Shop\Domain\Model\Shop;

class ProductShopService
{
    public function __construct(
        private ProductShopRepositoryInterface $productShopRepository
    ) {
    }

    /**
     * @throws DBConnectionException
     */
    public function __invoke(ProductShopDto $input): ProductShop|null
    {
        $productShop = $this->getProductShopData($input->product->getId(), $input->shop->getId(), $input->product->getGroupId());

        if ($input->remove) {
            $this->remove($productShop);

            return null;
        }

        if (null === $productShop) {
            return $this->create($input->product, $input->shop, $input->price);
        }

        return $this->update($productShop, $input->price);
    }

    /**
     * @throws DBConnectionException
     */
    private function getProductShopData(Identifier $productId, Identifier $shopId, Identifier $groupId): ProductShop|null
    {
        try {
            $productShopPagination = $this->productShopRepository->findProductsAndShopsOrFail([$productId], [$shopId], $groupId);
            $productShopPagination->setPagination(1, 1);

            return iterator_to_array($productShopPagination)[0];
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @throws DBConnectionException
     */
    private function remove(ProductShop|null $productShop): void
    {
        if (null === $productShop) {
            return;
        }

        $this->productShopRepository->remove($productShop);
    }

    /**
     * @throws DBConnectionException
     */
    private function update(ProductShop $productShop, Money $price): ProductShop
    {
        $price->isNull() ?: $productShop->setPrice($price);

        $this->productShopRepository->save($productShop);

        return $productShop;
    }

    /**
     * @throws DBConnectionException
     */
    private function create(Product $product, Shop $shop, Money $price): ProductShop
    {
        $productShopNew = ProductShop::fromPrimitives($product, $shop, $price->getValue());
        $this->productShopRepository->save($productShopNew);

        return $productShopNew;
    }
}
