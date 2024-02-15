<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersGetOrders;

use Common\Domain\Exception\LogicException;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use ListOrders\Domain\Ports\ListOrdersOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersGetOrders\Dto\ListOrdersGetOrdersDto;
use Order\Domain\Model\Order;

class ListOrdersGetOrdersService
{
    private PaginatorInterface $listOrderOrdersPaginator;

    public function __construct(
        private ListOrdersOrdersRepositoryInterface $listOrdersOrdersRepository
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(ListOrdersGetOrdersDto $input): array
    {
        $this->listOrderOrdersPaginator = $this->listOrdersOrdersRepository->findListOrderOrdersDataByIdOrFail($input->listOrderId, $input->groupId);
        $this->listOrderOrdersPaginator->setPagination($input->page->getValue(), $input->pageItems->getValue());

        return $this->getOrdersData($this->listOrderOrdersPaginator);
    }

    /**
     * @throws LogicException
     */
    public function getPaginationTotalPages(): int
    {
        if (!isset($this->listOrderOrdersPaginator)) {
            throw LogicException::fromMessage('Paginator is not initialized. Call first method __invoke.');
        }

        return $this->listOrderOrdersPaginator->getPagesTotal();
    }

    private function getOrdersData(PaginatorInterface $listOrderOrdersPaginator): array
    {
        $listOrderOrders = iterator_to_array($listOrderOrdersPaginator);

        return array_map($this->getOrderData(...), $listOrderOrders);
    }

    private function getOrderData(Order $order): array
    {
        return [
           'id' => $order->getId()->getValue(),
           'user_id' => $order->getUserId()->getValue(),
           'group_id' => $order->getGroupId()->getValue(),
           'description' => $order->getDescription()->getValue(),
           'amount' => $order->getAmount()->getValue(),
           'created_on' => $order->getCreatedOn()->format('Y-m-d H:i:s'),
           'product' => [
               'id' => $order->getProduct()->getId()->getValue(),
               'name' => $order->getProduct()->getName()->getValue(),
               'description' => $order->getProduct()->getDescription()->getValue(),
               'image' => $order->getProduct()->getImage()->getValue(),
               'created_on' => $order->getProduct()->getCreatedOn()->format('Y-m-d H:i:s'),
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
            'image' => $order->getShop()->getImage()->getValue(),
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
