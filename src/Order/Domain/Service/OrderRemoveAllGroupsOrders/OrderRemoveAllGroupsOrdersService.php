<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderRemoveAllGroupsOrders;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderRemoveAllGroupsOrders\Dto\OrderRemoveAllGroupsOrdersDto;
use Order\Domain\Service\OrderRemoveAllGroupsOrders\Dto\OrderRemoveAllGroupsOrdersOutputDto;

class OrderRemoveAllGroupsOrdersService
{
    private const ORDERS_PAGINATOR_PAGE_ITEMS = 100;

    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @throws DBConnectionException
     */
    public function __invoke(OrderRemoveAllGroupsOrdersDto $input): OrderRemoveAllGroupsOrdersOutputDto
    {
        $ordersIdRemoved = [];
        if (!empty($input->groupsIdToRemoveOrders)) {
            $ordersIdRemoved = $this->groupsOrdersRemove($input->groupsIdToRemoveOrders);
        }

        $ordersIdUserIdChanged = [];
        if (!empty($input->groupsIdToChangeOrdersUser) && null !== $input->userIdToSet) {
            $ordersIdUserIdChanged = $this->ordersChangeUserId($input->groupsIdToChangeOrdersUser, $input->userIdToSet);
        }

        return $this->createOrderRemoveALlGroupsOrdersOutputDto($ordersIdRemoved, $ordersIdUserIdChanged);
    }

    /**
     * @return Identifier[]
     *
     * @throws DBConnectionException
     */
    private function groupsOrdersRemove(array $groupIds): array
    {
        try {
            $ordersPaginator = $this->orderRepository->findGroupsOrdersOrFail($groupIds);

            $ordersIdRemoved = [];
            foreach ($ordersPaginator->getAllPages(self::ORDERS_PAGINATOR_PAGE_ITEMS) as $ordersIterator) {
                $orders = iterator_to_array($ordersIterator);
                $ordersIdRemoved[] = array_map(
                    fn (Order $order) => $order->getId(),
                    $orders
                );

                $this->orderRepository->remove($orders);
            }

            return array_merge(...$ordersIdRemoved);
        } catch (DBNotFoundException) {
            return [];
        }
    }

    /**
     * @return Identifier[]
     *
     * @throws DBConnectionException
     */
    private function ordersChangeUserId(array $groupsId, Identifier $userIdToSet): array
    {
        try {
            $ordersPaginator = $this->orderRepository->findGroupsOrdersOrFail($groupsId);

            foreach ($ordersPaginator->getAllPages(self::ORDERS_PAGINATOR_PAGE_ITEMS) as $orderIterator) {
                $orders = iterator_to_array($orderIterator);
                $ordersIdChangedUserId[] = array_map(
                    fn (Order $order) => $order->getId(),
                    $orders
                );

                array_walk(
                    $orders,
                    fn (Order $order) => $order->setUserId($userIdToSet)
                );

                $this->orderRepository->save($orders);
            }

            return array_merge(...$ordersIdChangedUserId);
        } catch (DBNotFoundException) {
            return [];
        }
    }

    /**
     * @param Identifier[] $ordersIdRemoved
     * @param Identifier[] $ordersIdChangedUserId
     */
    private function createOrderRemoveALlGroupsOrdersOutputDto(array $ordersIdRemoved, array $ordersIdChangedUserId): OrderRemoveAllGroupsOrdersOutputDto
    {
        return new OrderRemoveAllGroupsOrdersOutputDto($ordersIdRemoved, $ordersIdChangedUserId);
    }
}
