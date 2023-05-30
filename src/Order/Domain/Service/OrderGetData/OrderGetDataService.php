<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderGetData;

use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\String\Identifier;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderGetData\Dto\OrderGetDataDto;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;

class OrderGetDataService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private ProductShopRepositoryInterface $productShopRepository,
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(OrderGetDataDto $input): array
    {
        $ordersPaginator = $this->orderRepository->findOrdersByIdOrFail($input->ordersId, $input->groupId);
        $orders = iterator_to_array($ordersPaginator);
        $productsShops = $this->getProductsPricesByProductId($orders, $input->groupId);

        return array_map(
            fn (Order $order) => $this->getOrderData($order, $productsShops[$order->getProductId()->getValue()]->getPrice()),
            $orders
        );
    }

    /**
     * @param Order[] $orders
     *
     * @return ProductShop[]
     *
     * @throws DBNotFoundException
     */
    private function getProductsPricesByProductId(array $orders, Identifier $groupId): array
    {
        $productId = array_map(
            fn (Order $order) => $order->getProductId(),
            $orders
        );
        $ordersShopsId = array_map(
            fn (Order $order) => $order->getShopId(),
            $orders
        );
        $productsShopsPagination = $this->productShopRepository->findProductsAndShopsOrFail($productId, $ordersShopsId, $groupId);
        $productsShops = iterator_to_array($productsShopsPagination);

        return array_combine(
            array_map(
                fn (ProductShop $productShop) => $productShop->getProductId()->getValue(),
                $productsShops
            ),
            $productsShops
        );
    }

    private function getOrderData(Order $order, Money $price): array
    {
        return [
            'id' => $order->getId()->getValue(),
            'product_id' => $order->getProductId()->getValue(),
            'shop_id' => $order->getShopId()->getValue(),
            'user_id' => $order->getUserId()->getValue(),
            'group_id' => $order->getGroupId()->getValue(),
            'description' => $order->getDescription()->getValue(),
            'amount' => $order->getAmount()->getValue(),
            'created_on' => $order->getCreatedOn()->format('Y-m-d H:i:s'),
            'price' => $price->getValue(),
        ];
    }
}
