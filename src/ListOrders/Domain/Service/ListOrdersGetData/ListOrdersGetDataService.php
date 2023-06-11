<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersGetData;

use Common\Domain\Exception\LogicException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersGetData\Dto\ListOrdersGetDataDto;

class ListOrdersGetDataService
{
    public function __construct(
        private ListOrdersRepositoryInterface $listOrdersRepository,
    ) {
    }

    /**
     * @throws DBNotFoundException
     * @throws LogicException
     */
    public function __invoke(ListOrdersGetDataDto $input): array
    {
        $listsOrders = $this->findListOrder($input->groupId, $input->listOrdersId, $input->listOrdersNameStartsWith);

        return $this->getListOrderData($listsOrders);
    }

    /**
     * @return ListOrders[]
     *
     * @throws DBNotFoundException
     * @throws LogicException
     */
    private function findListOrder(Identifier $groupId, array $listOrdersId, NameWithSpaces $listOrdersNameStartsWith): array
    {
        if (!empty($listOrdersId)) {
            $listOrdersPaginator = $this->listOrdersRepository->findListOrderByIdOrFail($listOrdersId, $groupId);
        } elseif (!$listOrdersNameStartsWith->isNull()) {
            $listOrdersPaginator = $this->listOrdersRepository->findListOrderByNameStarsWithOrFail($listOrdersNameStartsWith, $groupId);
        } else {
            throw LogicException::fromMessage('listOrdersId and listOrdersNameStarsWith, are both null');
        }

        $listOrdersPaginator->setPagination(1, 100);

        return iterator_to_array($listOrdersPaginator);
    }

    private function getListOrderData(array $listsOrders): array
    {
        return array_map(
            fn (ListOrders $listOrders) => [
                'id' => $listOrders->getId()->getValue(),
                'user_id' => $listOrders->getUserId()->getValue(),
                'group_id' => $listOrders->getGroupId()->getValue(),
                'name' => $listOrders->getName()->getValue(),
                'description' => $listOrders->getDescription()->getValue(),
                'date_to_buy' => $listOrders->getDateToBuy()->getValue()->format('Y-m-d H:i:s'),
                'created_on' => $listOrders->getCreatedOn()->format('Y-m-d H:i:s'),
            ],
            $listsOrders
        );
    }
}
