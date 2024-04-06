<?php

declare(strict_types=1);

namespace Order\Domain\Ports\Repository;

use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Ports\Repository\RepositoryInterface;

interface OrderRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Order[] $orders
     *
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(array $orders): void;

    /**
     * @param Order[] $orders
     *
     * @throws DBConnectionException
     */
    public function remove(array $orders): void;

    /**
     * @param Identifier[] $ordersId
     *
     * @throws DBNotFoundException
     */
    public function findOrdersByIdOrFail(Identifier $groupId, array $ordersId, bool $orderAsc): PaginatorInterface;

    /**
     * @throws DBNotFoundException
     */
    public function findOrdersByListOrdersNameOrFail(Identifier $groupId, NameWithSpaces $listOrderName, bool $orderAsc): PaginatorInterface;

    /**
     * @throws DBNotFoundException
     */
    public function findOrdersByProductNameFilterOrFail(Identifier $groupId, ?Identifier $listOrdersId, Filter $filterText, bool $orderAsc): PaginatorInterface;

    /**
     * @throws DBNotFoundException
     */
    public function findOrdersByShopNameFilterOrFail(Identifier $groupId, ?Identifier $shopId, Filter $filterText, bool $orderAsc): PaginatorInterface;

    /**
     * @throws DBNotFoundException
     */
    public function findOrdersByGroupIdOrFail(Identifier $groupId, bool $orderAsc): PaginatorInterface;

    /**
     * @throws DBNotFoundException
     */
    public function findOrdersByListOrdersIdProductIdAndShopIdOrFail(Identifier $groupId, Identifier $listOrdersId, array $productsId, array $shopsId): PaginatorInterface;

    /**
     * @throws DBNotFoundException
     */
    public function findOrdersByListOrdersIdOrFail(Identifier $listOrderId, Identifier $groupId, bool $orderAsc): PaginatorInterface;
}
