<?php

declare(strict_types=1);

namespace ListOrders\Domain\Ports;

use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Ports\Repository\RepositoryInterface;
use ListOrders\Domain\Model\ListOrders;

interface ListOrdersRepositoryInterface extends RepositoryInterface
{
    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(ListOrders $orders): void;

    /**
     * @throws DBConnectionException
     */
    public function remove(ListOrders $orders): void;

    /**
     * @param Identifier[] $ListsOrdersId
     *
     * @throws DBNotFoundException
     */
    public function findListOrderByIdOrFail(array $ListsOrdersId): PaginatorInterface;

    /**
     * @throws DBNotFoundException
     */
    public function findListOrderByNameStarsWithOrFail(string $listsOrdersNameStarsWith): PaginatorInterface;
}
