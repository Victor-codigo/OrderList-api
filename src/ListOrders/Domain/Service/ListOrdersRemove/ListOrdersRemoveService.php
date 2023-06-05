<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersRemove;

use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersRemove\Dto\ListOrdersRemoveDto;

class ListOrdersRemoveService
{
    public function __construct(
        private ListOrdersRepositoryInterface $listOrdersRepository
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws DBConnectionException
     */
    public function __invoke(ListOrdersRemoveDto $input): ListOrders
    {
        $listOrdersPaginator = $this->listOrdersRepository->findListOrderByIdOrFail([$input->listOrdersId], $input->groupId);
        $listOrdersPaginator->setPagination(1, 1);
        $listOrders = iterator_to_array($listOrdersPaginator)[0];

        $this->listOrdersRepository->remove($listOrders);

        return $listOrders;
    }
}
