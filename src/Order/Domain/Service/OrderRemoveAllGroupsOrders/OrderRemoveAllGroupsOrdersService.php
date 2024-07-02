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
    private const int ORDERS_PAGINATOR_PAGE_ITEMS = 100;

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
        if (!empty($input->groupsIdToChangeOrdersUser)) {
            $ordersIdUserIdChanged = $this->ordersChangeUserId($input->groupsIdToChangeOrdersUser);
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
     * @param array<int, array{group_id: Identifier, admin: Identifier}> $groupsIdAndAdminId
     *
     * @return Identifier[]
     *
     * @throws DBConnectionException
     */
    private function ordersChangeUserId(array $groupsIdAndAdminId): array
    {
        try {
            $groupsId = array_map(
                fn (array $groupIdAndAdminId) => $groupIdAndAdminId['group_id'],
                $groupsIdAndAdminId
            );
            $groupsIdAndAdminIdIndexedByGroupId = array_combine(
                array_map(
                    fn (Identifier $groupId) => $groupId->getValue(),
                    $groupsId
                ),
                array_map(
                    fn (array $groupIdAndAdminId) => $groupIdAndAdminId['admin'],
                    $groupsIdAndAdminId
                ),
            );

            $ordersPaginator = $this->orderRepository->findGroupsOrdersOrFail($groupsId);

            foreach ($ordersPaginator->getAllPages(self::ORDERS_PAGINATOR_PAGE_ITEMS) as $orderIterator) {
                $orders = iterator_to_array($orderIterator);
                $ordersIdChangedUserId[] = array_map(
                    fn (Order $order) => $order->getId(),
                    $orders
                );

                array_walk(
                    $orders,
                    function (Order $order) use ($groupsIdAndAdminIdIndexedByGroupId): void {
                        if (!isset($groupsIdAndAdminIdIndexedByGroupId[$order->getGroupId()->getValue()])) {
                            return;
                        }

                        $order->setUserId($groupsIdAndAdminIdIndexedByGroupId[$order->getGroupId()->getValue()]);
                    }
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
