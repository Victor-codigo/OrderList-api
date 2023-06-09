<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersRemoveOrder;

use ListOrders\Domain\Model\ListOrdersOrders;
use ListOrders\Domain\Ports\ListOrdersOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersRemoveOrder\Dto\ListOrdersRemoveOrderDto;

class ListOrdersRemoveOrderService
{
    public function __construct(
        private ListOrdersOrdersRepositoryInterface $listOrdersOrdersRepository
    ) {
    }

    /**
     * @return ListOrdersOrders[]
     *
     * @throws DBNotFoundException
     * @throws DBConnectionException
     */
    public function __invoke(ListOrdersRemoveOrderDto $input): array
    {
        $ordersPaginator = $this->listOrdersOrdersRepository->findListOrderOrdersByIdOrFail($input->listOrdersId, $input->groupId, $input->ordersId);
        $orders = iterator_to_array($ordersPaginator);

        $this->listOrdersOrdersRepository->remove($orders);

        return $orders;
    }
}
