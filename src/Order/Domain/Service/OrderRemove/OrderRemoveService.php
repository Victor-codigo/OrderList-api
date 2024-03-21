<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderRemove;

use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderRemove\Dto\OrderRemoveDto;

class OrderRemoveService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * @return Order[]
     *
     * @throws DBNotFoundException
     */
    public function __invoke(OrderRemoveDto $input): array
    {
        $ordersPaginator = $this->orderRepository->findOrdersByIdOrFail($input->groupId, $input->ordersId, true);
        $orders = iterator_to_array($ordersPaginator);

        $this->orderRepository->remove($orders);

        return $orders;
    }
}
