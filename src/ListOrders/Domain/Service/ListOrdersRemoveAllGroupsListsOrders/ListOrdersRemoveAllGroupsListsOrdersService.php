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
    private const int LISTS_ORDERS_PAGINATOR_PAGE_ITEMS = 100;

    public function __construct(
        private ListOrdersRepositoryInterface $listOrdersRepository,
    ) {
    }

    /**
     * @throws DBConnectionException
     */
    public function __invoke(ListOrdersRemoveAllGroupsListsOrdersDto $input): ListOrdersRemoveAllGroupsListOrdersOutputDto
    {
        $listsOrdersIdRemoved = [];
        if (!empty($input->groupsIdToRemoveListsOrders)) {
            $listsOrdersIdRemoved = $this->groupsListsOrdersRemove($input->groupsIdToRemoveListsOrders);
        }

        $listsOrdersIdUserIdChanged = [];
        if (!empty($input->groupsIdToChangeListsOrdersUser)) {
            $listsOrdersIdUserIdChanged = $this->listsOrdersChangeUserId($input->groupsIdToChangeListsOrdersUser);
        }

        return $this->createListOrdersRemoveALlGroupsListsOrdersOutputDto($listsOrdersIdRemoved, $listsOrdersIdUserIdChanged);
    }

    /**
     * @param Identifier[] $groupIds
     *
     * @return Identifier[]
     *
     * @throws DBConnectionException
     */
    private function groupsListsOrdersRemove(array $groupIds): array
    {
        try {
            $listsOrdersPaginator = $this->listOrdersRepository->findGroupsListsOrdersOrFail($groupIds);

            $listsOrdersIdRemoved = [];
            foreach ($listsOrdersPaginator->getAllPages(self::LISTS_ORDERS_PAGINATOR_PAGE_ITEMS) as $listsOrdersIterator) {
                $listsOrders = iterator_to_array($listsOrdersIterator);
                $listsOrdersIdRemoved[] = array_map(
                    fn (ListOrders $listOrders): Identifier => $listOrders->getId(),
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
     * @param array<int, array{group_id: Identifier, admin: Identifier}> $groupsIdAndAdminId
     *
     * @return Identifier[]
     *
     * @throws DBConnectionException
     */
    private function listsOrdersChangeUserId(array $groupsIdAndAdminId): array
    {
        try {
            $groupsId = array_map(
                fn (array $groupIdAndAdminId): Identifier => $groupIdAndAdminId['group_id'],
                $groupsIdAndAdminId
            );
            $groupsIdAndAdminIdIndexedByGroupId = array_combine(
                array_map(
                    fn (Identifier $groupId): ?string => $groupId->getValue(),
                    $groupsId
                ),
                array_map(
                    fn (array $groupIdAndAdminId): Identifier => $groupIdAndAdminId['admin'],
                    $groupsIdAndAdminId
                ),
            );

            $listsOrdersPaginator = $this->listOrdersRepository->findGroupsListsOrdersOrFail($groupsId);

            $listsOrdersIdChangedUserId = [];
            foreach ($listsOrdersPaginator->getAllPages(self::LISTS_ORDERS_PAGINATOR_PAGE_ITEMS) as $listOrdersIterator) {
                $listsOrders = iterator_to_array($listOrdersIterator);
                $listsOrdersIdChangedUserId[] = array_map(
                    fn (ListOrders $listOrders): Identifier => $listOrders->getId(),
                    $listsOrders
                );

                array_walk(
                    $listsOrders,
                    function (ListOrders $listOrders) use ($groupsIdAndAdminIdIndexedByGroupId): void {
                        if (!isset($groupsIdAndAdminIdIndexedByGroupId[$listOrders->getGroupId()->getValue()])) {
                            return;
                        }

                        $listOrders->setUserId($groupsIdAndAdminIdIndexedByGroupId[$listOrders->getGroupId()->getValue()]);
                    }
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
    private function createListOrdersRemoveALlGroupsListsOrdersOutputDto(array $listsOrdersIdRemoved, array $listsOrdersIdChangedUserId): ListOrdersRemoveAllGroupsListOrdersOutputDto
    {
        return new ListOrdersRemoveAllGroupsListOrdersOutputDto($listsOrdersIdRemoved, $listsOrdersIdChangedUserId);
    }
}
