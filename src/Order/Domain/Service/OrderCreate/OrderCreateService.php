<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderCreate\Dto\OrderCreateDto;
use Order\Domain\Service\OrderCreate\Dto\OrderDataServiceDto;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateListOrdersNotFoundException;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateProductNotFoundException;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateProductShopRepeatedException;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateProductsNotFoundException;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateShopNotFoundException;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;

class OrderCreateService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ListOrdersRepositoryInterface $listOrdersRepository,
        private ProductRepositoryInterface $productRepository,
        private ShopRepositoryInterface $shopRepository,
    ) {
    }

    /**
     * @return Order[]
     *
     * @throws OrderCreateProductsNotFoundException
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     * @throws OrderCreateProductShopRepeatedException
     */
    public function __invoke(OrderCreateDto $input): array
    {
        $listOrders = $this->getListOrders($input->groupId, $input->listOrdersId);
        $products = $this->getProducts($input->groupId, $input->orders);
        $shops = $this->getShops($input->groupId, $input->orders);
        $this->productAndShopAreNotRepeatedOrFail($input->groupId, $input->listOrdersId, $products, $shops);

        $orders = array_map(
            fn (OrderDataServiceDto $order): Order => $this->createOrder($input->groupId, $order, $listOrders, $products, $shops),
            $input->orders
        );

        $this->orderRepository->save($orders);

        return $orders;
    }

    /**
     * @param Product[] $products
     * @param Shop[]    $shops
     *
     * @throws OrderCreateProductShopRepeatedException
     */
    private function productAndShopAreNotRepeatedOrFail(Identifier $groupId, Identifier $listOrdersId, array $products, array $shops): void
    {
        $productsId = array_values(array_map(
            fn (Product $product): Identifier => $product->getId(),
            $products
        ));
        $shopsId = array_values(array_map(
            fn (Shop $shop): Identifier => $shop->getId(),
            $shops
        ));

        try {
            $this->orderRepository->findOrdersByListOrdersIdProductIdAndShopIdOrFail($groupId, $listOrdersId, $productsId, $shopsId);

            throw OrderCreateProductShopRepeatedException::fromMessage('Product and shop are already in the list of orders');
        } catch (DBNotFoundException) {
        }
    }

    /**
     * @throws OrderCreateListOrdersNotFoundException
     */
    private function getListOrders(Identifier $groupId, Identifier $listOrdersId): ListOrders
    {
        try {
            $listOrdersPagination = $this->listOrdersRepository->findListOrderByIdOrFail([$listOrdersId], $groupId);
            $listOrdersPagination->setPagination(1, 1);

            return iterator_to_array($listOrdersPagination)[0];
        } catch (DBNotFoundException) {
            throw OrderCreateListOrdersNotFoundException::fromMessage('List orders not found');
        }
    }

    /**
     * @param OrderDataServiceDto[] $orders
     *
     * @return Product[]
     *
     * @throws OrderCreateProductsNotFoundException
     */
    private function getProducts(Identifier $groupId, array $orders): array
    {
        $productsId = array_map(
            fn (OrderDataServiceDto $order): Identifier => $order->productId,
            $orders
        );

        try {
            $productsPagination = $this->productRepository->findProductsOrFail($groupId, $productsId);
            $productsPagination->setPagination(1);
            $products = iterator_to_array($productsPagination);

            if (count($orders) !== count($products)) {
                throw DBNotFoundException::fromMessage('Not all products have been found');
            }

            return array_combine(
                array_map(fn (Product $product): ?string => $product->getId()->getValue(), $products),
                $products
            );
        } catch (DBNotFoundException) {
            throw OrderCreateProductNotFoundException::fromMessage('Products not found');
        }
    }

    /**
     * @param OrderDataServiceDto[] $orders
     *
     * @return array<string, Shop>
     */
    private function getShops(Identifier $groupId, array $orders): array
    {
        $shopsId = array_map(
            fn (OrderDataServiceDto $order): Identifier => $order->shopId->toIdentifier(),
            $orders
        );

        try {
            $shopsPagination = $this->shopRepository->findShopsOrFail($groupId, $shopsId);
            $shopsPagination->setPagination(1);
            $shops = iterator_to_array($shopsPagination);
            $shopsIdUnique = array_unique($shopsId);

            if (count($shops) !== count($shopsIdUnique)) {
                throw DBNotFoundException::fromMessage('Not all shops have been found');
            }

            return array_combine(
                array_map(fn (Shop $shop): ?string => $shop->getId()->getValue(), $shops),
                $shops
            );
        } catch (DBNotFoundException) {
            $shopsIdNotNull = array_filter(
                $shopsId,
                fn (Identifier $shopId): bool => !$shopId->isNull(),
            );

            if (empty($shopsIdNotNull)) {
                return [];
            }

            throw OrderCreateShopNotFoundException::fromMessage('Shops not found');
        }
    }

    /**
     * @param Product[] $products
     * @param Shop[]    $shops
     */
    private function createOrder(Identifier $groupId, OrderDataServiceDto $order, ListOrders $listOrders, array $products, array $shops): Order
    {
        $orderId = $this->orderRepository->generateId();

        $shopId = null;
        if (!$order->shopId->isNull()) {
            $shopId = $shops[$order->shopId->getValue()];
        }

        return new Order(
            ValueObjectFactory::createIdentifier($orderId),
            $groupId,
            $order->userId,
            $order->description,
            $order->amount,
            false,
            $listOrders,
            $products[$order->productId->getValue()],
            $shopId
        );
    }
}
