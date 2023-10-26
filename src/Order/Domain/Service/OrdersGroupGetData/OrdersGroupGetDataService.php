<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrdersGroupGetData;

use Common\Domain\Exception\LogicException;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrdersGroupGetData\Dto\OrdersGroupGetDataDto;

class OrdersGroupGetDataService
{
    private PaginatorInterface $ordersGroupPaginator;

    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(OrdersGroupGetDataDto $input): array
    {
        $this->ordersGroupPaginator = $this->orderRepository->findOrdersGroupOrFail($input->groupId);
        $this->ordersGroupPaginator->setPagination($input->page->getValue(), $input->pageItems->getValue());

        return $this->getOrderData($this->ordersGroupPaginator);
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

    private function getOrderData(PaginatorInterface $listOrderOrdersPaginator): array
    {
        return array_map(
            fn (Order $order) => [
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
                'shop' => [
                    'id' => $order->getShop()->getId()->getValue(),
                    'name' => $order->getShop()->getName()->getValue(),
                    'description' => $order->getShop()->getDescription()->getValue(),
                    'image' => $order->getShop()->getImage()->getValue(),
                    'created_on' => $order->getShop()->getCreatedOn()->format('Y-m-d H:i:s'),
                ],
            ],
            iterator_to_array($listOrderOrdersPaginator)
        );
    }
}
