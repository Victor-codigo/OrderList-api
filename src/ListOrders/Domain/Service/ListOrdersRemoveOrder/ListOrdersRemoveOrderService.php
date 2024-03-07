<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersRemoveOrder;

use Common\Domain\Config\AppConfig;
use ListOrders\Domain\Model\ListOrdersOrders;
use ListOrders\Domain\Ports\ListOrdersOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersRemoveOrder\Dto\ListOrdersRemoveOrderDto;

class ListOrdersRemoveOrderService
{
    private const LIST_ORDERS_ORDERS_REMOVE_MAX = AppConfig::ENDPOINT_LIST_ORDERS_REMOVE_MAX;

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
        $listsOrdersOrdersPaginator = $this->listOrdersOrdersRepository->findListOrderOrdersByIdOrFail($input->listOrdersId, $input->groupId);
        $listsOrdersOrdersPaginator->setPagination(1, self::LIST_ORDERS_ORDERS_REMOVE_MAX);
        $listsOrdersOrders = iterator_to_array($listsOrdersOrdersPaginator);

        $this->listOrdersOrdersRepository->remove($listsOrdersOrders);

        return $listsOrdersOrders;
    }
}
