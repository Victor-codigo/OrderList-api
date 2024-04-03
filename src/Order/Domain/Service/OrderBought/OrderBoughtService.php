<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderBought;

use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Order\Domain\Service\OrderBought\Dto\OrderBoughtDto;

class OrderBoughtService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    /**
     * @throws DBConnectionException
     */
    public function __invoke(OrderBoughtDto $input): Order
    {
        $ordersPaginator = $this->orderRepository->findOrdersByIdOrFail($input->groupId, [$input->orderId], true);
        $ordersPaginator->setPagination(1, 1);
        /** @var Order $order */
        $order = iterator_to_array($ordersPaginator)[0];
        $order->setBought($input->bought);

        $this->orderRepository->save([$order]);

        return $order;
    }
}
