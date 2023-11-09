<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderCreate;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderCreate\Dto\OrderCreateDto;
use Order\Domain\Service\OrderCreate\Dto\OrderDataServiceDto;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateProductNotFoundException;
use Order\Domain\Service\OrderCreate\Exception\OrderCreateProductsNotFoundException;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;

class OrderCreateService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
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
        $products = $this->getProducts($input->groupId, $input->orders);
        $shops = $this->getShops($input->groupId, $input->orders);

        $orders = array_map(
            fn (OrderDataServiceDto $order) => $this->createOrder($input->groupId, $order, $products, $shops),
            $input->orders
        );

        $this->orderRepository->save($orders);

        return $orders;
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
            $products = $this->productRepository->findProductsOrFail($productsId, $groupId);
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
            $shops = $this->shopRepository->findShopsOrFail($shopsId, $groupId);
            $shops->setPagination(1);
            $shopsArray = iterator_to_array($shops);

            return array_combine(
                array_map(fn (Shop $shop) => $shop->getId()->getValue(), $shopsArray),
                $shopsArray
            );
        } catch (DBNotFoundException) {
            return [];
        }
    }

    /**
     * @param Product[] $products
     * @param Shop[]    $shops
     */
    private function createOrder(Identifier $groupId, OrderDataServiceDto $order, array $products, array $shops): Order
    {
        $orderId = $this->orderRepository->generateId();

        $shopId = null;
        if (!$order->shopId->isNull()) {
            $shopId = $shops[$order->shopId->getValue()];
        }

        return new Order(
            ValueObjectFactory::createIdentifier($orderId),
            $order->userId,
            $groupId,
            $order->description,
            $order->amount,
            $order->unit,
            $products[$order->productId->getValue()],
            $shopId
        );
    }
}
