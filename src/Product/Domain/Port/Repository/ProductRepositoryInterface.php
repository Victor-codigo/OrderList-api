<?php

declare(strict_types=1);

namespace Product\Domain\Port\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\Group\Filter;
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
     * @return PaginatorInterface<int, Product>
     *
     * @throws DBNotFoundException
     */
    public function findProductsByGroupAndNameOrFail(Identifier $groupId, NameWithSpaces $name): PaginatorInterface;

    /**
     * @param Identifier[]|null $productsId
     * @param Identifier[]|null $shopsId
     *
     * @return PaginatorInterface<int, Product>
     *
     * @throws DBNotFoundException
     */
    public function findProductsOrFail(Identifier $groupId, ?array $productsId = null, ?array $shopsId = null, bool $orderAsc = true): PaginatorInterface;

    /**
     * @return PaginatorInterface<int, Product>
     *
     * @throws DBNotFoundException
     */
    public function findProductsByProductNameOrFail(Identifier $groupId, NameWithSpaces $productName, bool $orderAsc): PaginatorInterface;

    /**
     * @return PaginatorInterface<int, Product>
     *
     * @throws DBNotFoundException
     */
    public function findProductsByProductNameFilterOrFail(Identifier $groupId, Filter $productNameFilter, bool $orderAsc): PaginatorInterface;

    /**
     * @return PaginatorInterface<int, Product>
     *
     * @throws DBNotFoundException
     */
    public function findProductsByShopNameFilterOrFail(Identifier $groupId, Filter $shopNameFilter, bool $orderAsc): PaginatorInterface;

    /**
     * @param Identifier[] $groupsId
     *
     * @return PaginatorInterface<int, Product>
     *
     * @throws DBNotFoundException
     */
    public function findGroupsProductsOrFail(array $groupsId): PaginatorInterface;

    /**
     * @return string[]
     *
     * @throws DBNotFoundException
     */
    public function findGroupProductsFirstLetterOrFail(Identifier $groupId): array;
}
