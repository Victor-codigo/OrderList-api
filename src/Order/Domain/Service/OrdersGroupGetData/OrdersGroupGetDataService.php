<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrdersGroupGetData;

use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrdersGroupGetData\Dto\OrdersGroupGetDataDto;
use Product\Domain\Model\ProductShop;

class OrdersGroupGetDataService
{
    private PaginatorInterface $ordersGroupPaginator;

    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private string $productPublicImagePath,
        private string $shopPublicImagePath,
        private string $appProtocolAndDomain
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(OrdersGroupGetDataDto $input): array
    {
        $this->ordersGroupPaginator = $this->getOrdersGroup($input->groupId, $input->page, $input->pageItems, $input->orderAsc);

        return $this->getOrdersData($this->ordersGroupPaginator);
    }

    /**
     * @throws LogicException
     */
    public function getPaginationTotalPages(): int
    {
        if (!isset($this->ordersGroupPaginator)) {
            throw LogicException::fromMessage('Paginator is not initialized. Call first method __invoke.');
        }

        return $this->ordersGroupPaginator->getPagesTotal();
    }

    /**
     * @throws DBNotFoundException
     */
    private function getOrdersGroup(Identifier $groupId, PaginatorPage $page, PaginatorPageItems $pageItems, bool $orderAsc): PaginatorInterface
    {
        $this->ordersGroupPaginator = $this->orderRepository->findOrdersByGroupIdOrFail($groupId, $orderAsc);
        $this->ordersGroupPaginator->setPagination($page->getValue(), $pageItems->getValue());

        return $this->ordersGroupPaginator;
    }

    private function getOrdersData(PaginatorInterface $listOrdersPaginator): array
    {
        $listOrders = iterator_to_array($listOrdersPaginator);

        return array_map($this->getOrderData(...), $listOrders);
    }

    private function getOrderData(Order $order): array
    {
        return [
            'id' => $order->getId()->getValue(),
            'group_id' => $order->getGroupId()->getValue(),
            'list_orders_id' => $order->getListOrdersId()->getValue(),
            'user_id' => $order->getUserId()->getValue(),
            'description' => $order->getDescription()->getValue(),
            'amount' => $order->getAmount()->getValue(),
            'bought' => $order->getBought(),
            'created_on' => $order->getCreatedOn()->format('Y-m-d H:i:s'),
            'product' => [
                'id' => $order->getProduct()->getId()->getValue(),
                'name' => $order->getProduct()->getName()->getValue(),
                'description' => $order->getProduct()->getDescription()->getValue(),
                'image' => $order->getProduct()->getImage()->isNull()
                    ? null
                    : "{$this->appProtocolAndDomain}{$this->productPublicImagePath}/{$order->getProduct()->getImage()->getValue()}",
                'created_on' => "{$this->appProtocolAndDomain}{$this->productPublicImagePath}/{$order->getProduct()->getCreatedOn()->format('Y-m-d H:i:s')}",
            ],
            'shop' => $this->getProductShopData($order),
            'productShop' => $this->getProductShopPrice($order),
        ];
    }

    /**
     * @return array<{id: string, name: string, description: string, created_on: string}>
     */
    private function getProductShopData(Order $order): array
    {
        if ($order->getShopId()->isNull()) {
            return [];
        }

        return [
            'id' => $order->getShop()->getId()->getValue(),
            'name' => $order->getShop()->getName()->getValue(),
            'description' => $order->getShop()->getDescription()->getValue(),
            'image' => $order->getShop()->getImage()->isNull()
                ? null
                : "{$this->appProtocolAndDomain}{$this->shopPublicImagePath}/{$order->getShop()->getImage()->getValue()}",
            'created_on' => $order->getShop()->getCreatedOn()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return array<{price: float, unit: string}>
     */
    private function getProductShopPrice(Order $order): array
    {
        if ($order->getShopId()->isNull()) {
            return [];
        }

        /** @var ProductShop[] $productsShops */
        $productsShops = $order->getProduct()->getProductShop()->getValues();

        foreach ($productsShops as $productShop) {
            if ($productShop->getShopId()->equalTo($order->getShopId())) {
                return [
                    'price' => $productShop->getPrice()->getValue(),
                    'unit' => $productShop->getUnit()->getValue(),
                ];
            }
        }

        return [];
    }
}
