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
     */
    public function __invoke(OrderCreateDto $input): array
    {
        $listsOrders = $this->getListOrders($input->groupId, $input->orders);
        $products = $this->getProducts($input->groupId, $input->orders);
        $shops = $this->getShops($input->groupId, $input->orders);

        $orders = array_map(
            fn (OrderDataServiceDto $order) => $this->createOrder($input->groupId, $order, $listsOrders, $products, $shops),
            $input->orders
        );

        $this->orderRepository->save($orders);

        return $orders;
    }

    /**
     * @return ListOrders[]
     *
     * @throws OrderCreateListOrdersNotFoundException
     */
    private function getListOrders(Identifier $groupId, array $orders): array
    {
        $listOrdersId = array_map(
            fn (OrderDataServiceDto $order) => $order->listOrdersId,
            $orders
        );

        try {
            $listOrdersPagination = $this->listOrdersRepository->findListOrderByIdOrFail($listOrdersId, $groupId);
            $listOrdersPagination->setPagination(1);
            $listsOrders = iterator_to_array($listOrdersPagination);
            $listOrdersIdUnique = array_unique($listOrdersId);

            if (count($listsOrders) !== count($listOrdersIdUnique)) {
                throw DBNotFoundException::fromMessage('Not all list orders have been found');
            }

            return array_combine(
                array_map(fn (ListOrders $listOrders) => $listOrders->getId()->getValue(), $listsOrders),
                $listsOrders
            );
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
            fn (OrderDataServiceDto $order) => $order->productId,
            $orders
        );

        try {
            $products = $this->productRepository->findProductsOrFail($groupId, $productsId);
            $products->setPagination(1);
            $productsArray = iterator_to_array($products);

            if (count($orders) !== count($productsArray)) {
                throw DBNotFoundException::fromMessage('Not all products have been found');
            }

            return array_combine(
                array_map(fn (Product $product) => $product->getId()->getValue(), $productsArray),
                $productsArray
            );
        } catch (DBNotFoundException) {
            throw OrderCreateProductNotFoundException::fromMessage('Products not found');
        }
    }

    /**
     * @param OrderDataServiceDto[] $orders
     *
     * @return Product[]
     */
    private function getShops(Identifier $groupId, array $orders): array
    {
        $shopsId = array_map(
            fn (OrderDataServiceDto $order) => $order->shopId->toIdentifier(),
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
                array_map(fn (Shop $shop) => $shop->getId()->getValue(), $shops),
                $shops
            );
        } catch (DBNotFoundException) {
            $shopsIdNotNull = array_filter(
                $shopsId,
                fn (Identifier $shopId) => !$shopId->isNull(),
            );

            if (empty($shopsIdNotNull)) {
                return [];
            }

            throw OrderCreateShopNotFoundException::fromMessage('Shops not found');
        }
    }

    /**
     * @param ListOrders[] $listsOrders
     * @param Product[]    $products
     * @param Shop[]       $shops
     */
    private function createOrder(Identifier $groupId, OrderDataServiceDto $order, array $listsOrders, array $products, array $shops): Order
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
            $listsOrders[$order->listOrdersId->getValue()],
            $products[$order->productId->getValue()],
            $shopId
        );
    }
}
