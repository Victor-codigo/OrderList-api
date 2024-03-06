<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersRemove;

use Common\Domain\Config\AppConfig;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersRemove\Dto\ListOrdersRemoveDto;

class ListOrdersRemoveService
{
    private const LIST_ORDERS_REMOVE_MAX = AppConfig::ENDPOINT_LIST_ORDERS_REMOVE_MAX;

    public function __construct(
        private ListOrdersRepositoryInterface $listOrdersRepository
    ) {
    }

    /**
     * @return ListOrders[]
     *
     * @throws DBNotFoundException
     * @throws DBConnectionException
     */
    public function __invoke(ListOrdersRemoveDto $input): array
    {
        $listOrdersPaginator = $this->listOrdersRepository->findListOrderByIdOrFail($input->listsOrdersId, $input->groupId);
        $listOrdersPaginator->setPagination(1, self::LIST_ORDERS_REMOVE_MAX);
        $listsOrders = iterator_to_array($listOrdersPaginator);

        $this->listOrdersRepository->remove($listsOrders);

        return $listsOrders;
    }
}
