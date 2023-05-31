<?php

declare(strict_types=1);

namespace ListOrders\Domain\Ports;

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
}
