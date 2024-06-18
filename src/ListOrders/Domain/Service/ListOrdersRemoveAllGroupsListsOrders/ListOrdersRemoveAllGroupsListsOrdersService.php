<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\Dto\ListOrdersRemoveAllGroupsListOrdersOutputDto;
use ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\Dto\ListOrdersRemoveAllGroupsListsOrdersDto;

class ListOrdersRemoveAllGroupsListsOrdersService
{
    private const LIST_ORDERS_PAGINATOR_PAGE_ITEMS = 100;

    public function __construct(
        private ListOrdersRepositoryInterface $listOrdersRepository
    ) {
    }

    /**
     * @throws DBConnectionException
     */
    public function __invoke(ListOrdersRemoveAllGroupsListsOrdersDto $input): ListOrdersRemoveAllGroupsListOrdersOutputDto
    {
        $listsOrdersIdRemoved = [];
        if (!empty($input->groupsIdToRemoveListsOrders)) {
            $listsOrdersIdRemoved = $this->groupsOrdersRemove($input->groupsIdToRemoveListsOrders);
        }

        $listOrdersIdUserIdChanged = [];
        if (!empty($input->groupsIdToChangeListsOrdersUser) && null !== $input->userIdToSet) {
            $listOrdersIdUserIdChanged = $this->listOrdersChangeUserId($input->groupsIdToChangeListsOrdersUser, $input->userIdToSet);
        }

        return $this->createListOrdersRemoveAllGroupsListOrdersOutputDto($listsOrdersIdRemoved, $listOrdersIdUserIdChanged);
    }

    /**
     * @return Identifier[]
     *
     * @throws DBConnectionException
     */
    private function groupsOrdersRemove(array $groupIds): array
    {
        try {
            $listsOrdersPaginator = $this->listOrdersRepository->findGroupsListsOrdersOrFail($groupIds);

            $listsOrdersIdRemoved = [];
            foreach ($listsOrdersPaginator->getAllPages(self::LIST_ORDERS_PAGINATOR_PAGE_ITEMS) as $listsOrdersIterator) {
                $listsOrders = iterator_to_array($listsOrdersIterator);
                $listsOrdersIdRemoved[] = array_map(
                    fn (ListOrders $listOrders) => $listOrders->getId(),
                    $listsOrders
                );

                $this->listOrdersRepository->remove($listsOrders);
            }

            return array_merge(...$listsOrdersIdRemoved);
        } catch (DBNotFoundException) {
            return [];
        }
    }

    /**
     * @return Identifier[]
     *
     * @throws DBConnectionException
     */
    private function listOrdersChangeUserId(array $groupsId, Identifier $userIdToSet): array
    {
        try {
            $listsOrdersPaginator = $this->listOrdersRepository->findGroupsListsOrdersOrFail($groupsId);

            foreach ($listsOrdersPaginator->getAllPages(self::LIST_ORDERS_PAGINATOR_PAGE_ITEMS) as $listsOrderIterator) {
                $listsOrders = iterator_to_array($listsOrderIterator);
                $listsOrdersIdChangedUserId[] = array_map(
                    fn (ListOrders $listOrders) => $listOrders->getId(),
                    $listsOrders
                );

                array_walk(
                    $listsOrders,
                    fn (ListOrders $listOrders) => $listOrders->setUserId($userIdToSet)
                );

                $this->listOrdersRepository->save($listsOrders);
            }

            return array_merge(...$listsOrdersIdChangedUserId);
        } catch (DBNotFoundException) {
            return [];
        }
    }

    /**
     * @param Identifier[] $listsOrdersIdRemoved
     * @param Identifier[] $listsOrdersIdChangedUserId
     */
    private function createListOrdersRemoveAllGroupsListOrdersOutputDto(array $listsOrdersIdRemoved, array $listsOrdersIdChangedUserId): ListOrdersRemoveAllGroupsListOrdersOutputDto
    {
        return new ListOrdersRemoveAllGroupsListOrdersOutputDto($listsOrdersIdRemoved, $listsOrdersIdChangedUserId);
    }
}
