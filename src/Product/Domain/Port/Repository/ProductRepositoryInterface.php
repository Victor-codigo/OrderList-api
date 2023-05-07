<?php

declare(strict_types=1);

namespace Product\Domain\Port\Repository;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Ports\Repository\RepositoryInterface;
use Product\Domain\Model\Product;

interface ProductRepositoryInterface extends RepositoryInterface
{
    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(Product $product): void;

    /**
     * @param Product[] $products
     *
     * @throws DBConnectionException
     */
    public function remove(array $products): void;

    /**
     * @throws DBNotFoundException
     */
    public function findProductsByGroupAndNameOrFail(Identifier $groupId, NameWithSpaces $name): PaginatorInterface;

    /**
     * @param Identifier[]|null $productId
     * @param Identifier[]|null $shops
     *
     * @throws DBNotFoundException
     */
    public function findProductsOrFail(array|null $productsId = null, Identifier|null $groupId = null, array|null $shopsId = null, string|null $productNameStartsWith = null): PaginatorInterface;
}
