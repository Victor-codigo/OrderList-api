<?php

declare(strict_types=1);

namespace Product\Domain\Port\Repository;

use Common\Domain\Ports\Repository\RepositoryInterface;
use Product\Domain\Model\Product;

interface ProductShopRepositoryInterface extends RepositoryInterface
{
    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(Product $product): void;

    /**
     * @throws DBConnectionException
     */
    public function remove(Product $product): void;
}
