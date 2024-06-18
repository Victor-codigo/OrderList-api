<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersModify;

use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersModify\Dto\ListOrdersModifyDto;

class ListOrdersModifyService
{
    public function __construct(
        private ListOrdersRepositoryInterface $listOrdersRepository
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function __invoke(ListOrdersModifyDto $input): ListOrders
    {
        $listOrdersPagination = $this->listOrdersRepository->findListOrderByIdOrFail([$input->listOrdersId], $input->groupId);
        /** @var ListOrders $listOrders */
        $listOrders = iterator_to_array($listOrdersPagination)[0];

        $listOrders
            ->setUserId($input->userId)
            ->setName($input->name)
            ->setDescription($input->description)
            ->setDateToBuy($input->dateToBuy);

        $this->listOrdersRepository->save([$listOrders]);

        return $listOrders;
    }
}
