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
     * @param ProductShop $productsShops
     *
     * @throws DBConnectionException
     */
    public function save(array $productsShops): void;

    /**
     * @param ProductShop[] $productsShops
     *
     * @throws DBConnectionException
     */
    public function remove(array $productsShops): void;

    /**
     * @param Identifier[] $productId
     * @param Identifier[] $shopId
     *
     * @throws DBNotFoundException
     */
    public function findProductsAndShopsOrFail(?array $productId = null, ?array $shopId = null, ?Identifier $groupId = null): PaginatorInterface;
}
