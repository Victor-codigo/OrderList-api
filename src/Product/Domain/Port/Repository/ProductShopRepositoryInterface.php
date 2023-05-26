<?php

declare(strict_types=1);

namespace Product\Domain\Port\Repository;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Ports\Repository\RepositoryInterface;
use Product\Domain\Model\ProductShop;

interface ProductShopRepositoryInterface extends RepositoryInterface
{
    /**
     * @throws DBConnectionException
     */
    public function save(ProductShop $ProductShop): void;

    /**
     * @throws DBNotFoundException
     */
    public function findProductsAndShopsOrFail(Identifier|null $productId = null, Identifier|null $shopId = null, Identifier|null $groupId = null): PaginatorInterface;
}
