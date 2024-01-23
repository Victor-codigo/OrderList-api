<?php

declare(strict_types=1);

namespace Product\Domain\Service\ProductSetShopPrice;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\String\Identifier;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;
use Product\Domain\Service\ProductSetShopPrice\Dto\ProductSetShopPriceDto;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;

class ProductSetShopPriceService
{
    public function __construct(
        private ProductShopRepositoryInterface $productShopRepository,
        private ProductRepositoryInterface $productRepository,
        private ShopRepositoryInterface $shopRepository
    ) {
    }

    /**
     * @return ProductShop[]
     */
    public function __invoke(ProductSetShopPriceDto $input): array
    {
        $productsShops = $this->getProductShops($input->productsId, $input->shopsId, $input->groupId);
        $productsShopsToAdd = $this->createProductShopsToAdd($input->groupId, $productsShops, $input->productsId, $input->shopsId, $input->prices);
        $productsShopsModified = $this->modifyProductShops($productsShops, $input->productsId, $input->shopsId, $input->prices);
        $productsShopsToRemove = $this->getProductShopsToRemove($productsShops, $input->productsId, $input->shopsId);
        $productsShopsModifiedAndToAdd = array_merge($productsShopsModified, $productsShopsToAdd);

        $this->productShopRepository->remove($productsShopsToRemove);
        $this->productShopRepository->save($productsShopsModifiedAndToAdd);

        return $productsShopsModifiedAndToAdd;
    }

    /**
     * @param Identifier[] $productsId
     * @param Identifier[] $shopsId
     *
     * @return ProductShop[]
     */
    private function getProductShops(array $productsId, array $shopsId, Identifier $groupId): array
    {
        try {
            $productsShopsPaginator = $this->productShopRepository->findProductsAndShopsOrFail($productsId, $shopsId, $groupId);

            return iterator_to_array($productsShopsPaginator);
        } catch (DBNotFoundException) {
            return [];
        }
    }

    /**
     * @param ProductShop[] $productsShops
     * @param Identifier[]  $productsId
     * @param Identifier[]  $shopsId
     * @param Money[]       $prices
     *
     * @return ProductShop[]
     */
    private function modifyProductShops(array $productsShops, array $productsId, array $shopsId, array $prices): array
    {
        $productsShopsFilter = function (ProductShop $productShop) use ($productsId, $shopsId, $prices) {
            foreach ($productsId as $index => $productId) {
                if ($productShop->getProductId() != $productId) {
                    continue;
                }

                if ($productShop->getShopId() != $shopsId[$index]) {
                    continue;
                }

                $productShop->setPrice($prices[$index]);

                return true;
            }

            return false;
        };

        return array_values(array_filter($productsShops, $productsShopsFilter));
    }

    /**
     * @param ProductShop[] $productsShops
     * @param Identifier[]  $productsId
     * @param Identifier[]  $shopsId
     *
     * @return productShop[]
     */
    private function getProductShopsToRemove(array $productsShops, array $productsId, array $shopsId): array
    {
        $productsShopsFilter = function (ProductShop $productShop) use ($productsId, $shopsId) {
            foreach ($productsId as $index => $productId) {
                if ($productShop->getProductId() == $productId
                && $productShop->getShopId() == $shopsId[$index]) {
                    return false;
                }
            }

            return true;
        };

        return array_values(array_filter($productsShops, $productsShopsFilter));
    }

    /**
     * @param ProductShop[] $productsShops
     * @param Identifier[]  $productsId
     * @param Identifier[]  $shopsId
     *
     * @return productShop[]
     */
    private function createProductShopsToAdd(Identifier $groupId, array $productsShops, array $productsId, array $shopsId, array $prices): array
    {
        $productsIdAndShopsIdPrices = array_map(
            fn (Identifier $productId, Identifier $shopId, Money $price) => ['productId' => $productId, 'shopId' => $shopId, 'price' => $price],
            $productsId, $shopsId, $prices
        );

        $productsIdAndShopsIdPricesFilterCallback = function (array $productIdAndShopIdPrice) use ($productsShops) {
            foreach ($productsShops as $productShop) {
                if ($productShop->getProductId() != $productIdAndShopIdPrice['productId']) {
                    continue;
                }

                return $productShop->getShopId() != $productIdAndShopIdPrice['shopId'];
            }

            return true;
        };

        $productsIdAndShopsIdPricesNew = array_filter($productsIdAndShopsIdPrices, $productsIdAndShopsIdPricesFilterCallback);

        return $this->createProductsShopsFromArray($groupId, $productsIdAndShopsIdPricesNew);
    }

    /**
     * @param array<{productId: Identifier, shopÌd: Identifier, price: Money}> $productsIdAndShopsIdPrice
     *
     * @return ProductShop[]
     */
    private function createProductsShopsFromArray(Identifier $groupId, array $productsIdAndShopsIdPrice): array
    {
        if (empty($productsIdAndShopsIdPrice)) {
            return [];
        }

        $productsDb = $this->getProductsFromDb($groupId, array_column($productsIdAndShopsIdPrice, 'productId'));
        $shopsDb = $this->getShopsFromDb($groupId, array_column($productsIdAndShopsIdPrice, 'shopId'));

        $createProductShopCallback = function (array $productIdAndShopIdPrice) use ($productsDb, $shopsDb) {
            $productId = $productIdAndShopIdPrice['productId']->getValue();
            $shopId = $productIdAndShopIdPrice['shopId']->getValue();

            if (!array_key_exists($productId, $productsDb)) {
                return null;
            }

            if (!array_key_exists($shopId, $shopsDb)) {
                return null;
            }

            return new ProductShop($productsDb[$productId], $shopsDb[$shopId], $productIdAndShopIdPrice['price']);
        };

        $productsShopsCreated = array_map($createProductShopCallback, $productsIdAndShopsIdPrice);

        return array_values(array_filter($productsShopsCreated));
    }

    /**
     * @param Identifier[] $productsId
     *
     * @return array<string, Product> Index => Product id
     */
    private function getProductsFromDb(Identifier $groupId, array $productsId): array
    {
        try {
            $productsPaginator = $this->productRepository->findProductsOrFail($groupId, $productsId);

            return $this->productsOrShopsById(iterator_to_array($productsPaginator));
        } catch (DBNotFoundException) {
            return [];
        }
    }

    /**
     * @param Identifier[] $shopsId
     *
     * @return array<string, Shop> Index => shop id
     */
    private function getShopsFromDb(Identifier $groupId, array $shopsId): array
    {
        try {
            $shopPaginator = $this->shopRepository->findShopsOrFail($groupId, $shopsId);

            return $this->productsOrShopsById(iterator_to_array($shopPaginator));
        } catch (DBNotFoundException) {
            return [];
        }
    }

    /**
     * @param Product[]|Shop[] $productsOrShops
     *
     * @return array<string, Product|Shop>
     */
    private function productsOrShopsById(array $productsOrShops): array
    {
        return array_combine(
            array_map(
                fn (Product|Shop $shop) => $shop->getId()->getValue(),
                $productsOrShops
            ),
            $productsOrShops
        );
    }
}
