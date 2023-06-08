<?php

declare(strict_types=1);

namespace ListOrders\Domain\Ports;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Ports\Repository\RepositoryInterface;
use ListOrders\Domain\Model\ListOrdersOrders;

interface ListOrdersOrdersRepositoryInterface extends RepositoryInterface
{
    /**
     * @param ListOrdersOrders[] $listOrdersOrders
     *
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(array $listOrdersOrders): void;

    /**
     * @param ListOrdersOrders[] $listOrdersOrders
     *
     * @throws DBConnectionException
     */
    public function remove(array $listOrdersOrders): void;

    /**
     * @throws DBNotFoundException
     */
    public function findListOrderOrdersByIdOrFail(Identifier $listOrdersId, Identifier $groupId): PaginatorInterface;
}
