<?php

declare(strict_types=1);

namespace Shop\Domain\Port\Repository;

use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Ports\Repository\RepositoryInterface;
use Shop\Domain\Model\Shop;

interface ShopRepositoryInterface extends RepositoryInterface
{
    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(Shop $shops): void;

    /**
     * @param Shop[] $shops
     *
     * @throws DBConnectionException
     */
    public function remove(array $shops): void;

    /**
     * @param Identifier[]|null $shopsId
     * @param Identifier[]|null $productsId
     *
     * @return PaginatorInterface<int, Shop>
     *
     * @throws DBNotFoundException
     */
    public function findShopsOrFail(Identifier $groupId, ?array $shopsId = null, ?array $productsId = null, bool $orderAsc = true): PaginatorInterface;

    /**
     * @param Identifier[] $groupsId
     *
     * @return PaginatorInterface<int, Shop>
     *
     * @throws DBNotFoundException
     */
    public function findGroupsShopsOrFail(array $groupsId): PaginatorInterface;

    /**
     * @return PaginatorInterface<int, Shop>
     *
     * @throws DBNotFoundException
     */
    public function findShopByShopNameOrFail(Identifier $groupId, NameWithSpaces $shopName, bool $orderAsc = true): PaginatorInterface;

    /**
     * @return PaginatorInterface<int, Shop>
     *
     * @throws DBNotFoundException
     */
    public function findShopByShopNameFilterOrFail(Identifier $groupId, Filter $shopNameFilter, bool $orderAsc = true): PaginatorInterface;

    /**
     * @return string[]
     *
     * @throws DBNotFoundException
     */
    public function findGroupShopsFirstLetterOrFail(Identifier $groupId): array;
}
