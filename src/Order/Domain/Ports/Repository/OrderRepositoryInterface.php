<?php

declare(strict_types=1);

namespace Order\Domain\Ports\Repository;

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
}
