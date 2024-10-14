<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersGetPrice;

use Common\Domain\Config\AppConfig;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Float\Money;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Service\ListOrdersGetPrice\Dto\ListOrdersGetPriceDto;
use ListOrders\Domain\Service\ListOrdersGetPrice\Dto\ListOrdersGetPriceOutputDto;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;

class ListOrdersGetPriceService
{
    private const int LIST_ORDERS_MAX_ORDERS = AppConfig::LIST_ORDERS_MAX_ORDERS;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ProductShopRepositoryInterface $productShopRepository,
    ) {
    }

    public function __invoke(ListOrdersGetPriceDto $input): ListOrdersGetPriceOutputDto
    {
        $listOrdersPagination = $this->orderRepository->findOrdersByListOrdersIdOrFail($input->listOrdersId, $input->groupId, true);
        $listOrdersPagination->setPagination(1, self::LIST_ORDERS_MAX_ORDERS);
        $orders = iterator_to_array($listOrdersPagination);
        $productShopsPagination = $this->getProductsShopsPrices($orders, $input->groupId);
        /** @var ProductShop[] $productsShops */
        $productsShops = iterator_to_array($productShopsPagination);

        return new ListOrdersGetPriceOutputDto(
            $this->calculateListOrdersPrice($orders, $productsShops, false),
            $this->calculateListOrdersPrice($orders, $productsShops, true)
        );
    }

    /**
     * @param Order[] $orders
     *
     * @return PaginatorInterface<int, ProductShop>|\ArrayIterator<int, Order>
     *
     * @throws DBNotFoundException
     */
    private function getProductsShopsPrices(array $orders, Identifier $groupId): PaginatorInterface|\ArrayIterator
    {
        $productsId = array_map(
            fn (Order $order): Identifier => $order->getProductId(),
            $orders
        );
        $shopsId = array_map(
            fn (Order $order): Identifier => $order->getShopId(),
            $orders
        );

        try {
            return $this->productShopRepository->findProductsAndShopsOrFail($productsId, $shopsId, $groupId);
        } catch (DBNotFoundException $e) {
            return new \ArrayIterator([]);
        }
    }

    /**
     * @param Order[]       $orders
     * @param ProductShop[] $productsShops
     */
    private function calculateListOrdersPrice(array $orders, array $productsShops, bool $bought): Money
    {
        $totalPrice = array_reduce(
            $orders,
            fn (float $total, Order $order): float => $total + $this->calculateOrderPrice($order, $productsShops, $bought),
            0
        );

        return ValueObjectFactory::createMoney($totalPrice);
    }

    /**
     * @param ProductShop[] $productsShops
     */
    private function calculateOrderPrice(Order $order, array $productsShops, bool $bought): float
    {
        $productShop = $this->getProductShop($productsShops, $order->getProductId(), $order->getShopId());

        if (null === $productShop) {
            return 0;
        }

        if ($productShop->getPrice()->isNull()) {
            return 0;
        }

        if ($bought && !$order->getBought()) {
            return 0;
        }

        return $productShop->getPrice()->getValue() * $order->getAmount()->getValue();
    }

    /**
     * @param ProductShop[] $productsShops
     */
    private function getProductShop(array $productsShops, Identifier $productId, Identifier $shopId): ?ProductShop
    {
        $productShop = array_filter(
            $productsShops,
            fn (ProductShop $productShop): bool => $productShop->getProductId()->equalTo($productId)
                                         && $productShop->getShopId()->equalTo($shopId)
        );

        return empty($productShop) ? null : reset($productShop);
    }
}
