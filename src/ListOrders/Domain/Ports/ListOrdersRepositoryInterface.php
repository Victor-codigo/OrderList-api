<?php

declare(strict_types=1);

namespace ListOrders\Domain\Ports;

use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Ports\Repository\RepositoryInterface;
use ListOrders\Domain\Model\ListOrders;

interface ListOrdersRepositoryInterface extends RepositoryInterface
{
    /**
     * @param ListOrders[] $listsOrders
     *
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(array $listsOrders): void;

    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function saveListOrdersAndOrders(ListOrders $listOrders): void;

    /**
     * @param ListOrders[] $orders
     *
     * @throws DBConnectionException
     */
    public function remove(array $orders): void;

    /**
     * @param Identifier[] $ListsOrdersId
     *
     * @throws DBNotFoundException
     */
    public function findListOrderByIdOrFail(array $ListsOrdersId, ?Identifier $groupId = null): PaginatorInterface;

    /**
     * @throws DBNotFoundException
     */
    public function findListOrdersGroup(Identifier $groupId, bool $orderAsc): PaginatorInterface;

    /**
     * @throws DBNotFoundException
     */
    public function findListOrderByListOrdersNameFilterOrFail(Identifier $groupId, Filter $filterText, bool $orderAsc): PaginatorInterface;

    /**
     * @throws DBNotFoundException
     */
    public function findListOrderByProductNameFilterOrFail(Identifier $groupId, Filter $filterText, bool $orderAsc): PaginatorInterface;

    /**
     * @throws DBNotFoundException
     */
    public function findListOrderByShopNameFilterOrFail(Identifier $groupId, Filter $filterText, bool $orderAsc): PaginatorInterface;

    /**
     * @param Identifier[] $groupsId
     *
     * @throws DBNotFoundException
     */
    public function findGroupsListsOrdersOrFail(array $groupsId): PaginatorInterface;

    /**
     * @throws DBNotFoundException
     */
    public function findGroupListOrdersFirstLetterOrFail(Identifier $groupId): array;
}
