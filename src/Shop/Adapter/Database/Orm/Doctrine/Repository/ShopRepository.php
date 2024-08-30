<?php

declare(strict_types=1);

namespace Shop\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\EntityIdentityCollisionException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Shop\Domain\Model\Shop;
use Shop\Domain\Port\Repository\ShopRepositoryInterface;

class ShopRepository extends RepositoryBase implements ShopRepositoryInterface
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        PaginatorInterface $paginator
    ) {
        parent::__construct($managerRegistry, Shop::class, $paginator);
    }

    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    #[\Override]
    public function save(Shop $shop): void
    {
        try {
            $this->objectManager->persist($shop);
            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException|EntityIdentityCollisionException $e) {
            throw DBUniqueConstraintException::fromId($shop->getId()->getValue(), $e->getCode());
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param Shop[] $shops
     *
     * @throws DBConnectionException
     */
    #[\Override]
    public function remove(array $shops): void
    {
        try {
            foreach ($shops as $shop) {
                $this->objectManager->remove($shop);
            }

            $this->objectManager->flush();
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param Identifier[]|null $shopsId
     * @param Identifier[]|null $productsId
     *
     * @throws DBNotFoundException
     */
    #[\Override]
    public function findShopsOrFail(Identifier $groupId, ?array $shopsId = null, ?array $productsId = null, bool $orderAsc = true): PaginatorInterface
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('shop')
            ->from(Shop::class, 'shop');

        $queryBuilder
            ->where('shop.groupId = :groupId')
            ->setParameter('groupId', $groupId);

        if (null !== $shopsId) {
            $queryBuilder
                ->andWhere('shop.id IN (:shopsId)')
                ->setParameter('shopsId', $shopsId);
        }

        if (null !== $productsId) {
            $queryBuilder
                ->leftJoin(ProductShop::class, 'productShop', Join::WITH, 'shop.id = productShop.shopId')
                ->leftJoin(Product::class, 'product', Join::WITH, 'productShop.productId = product.id')
                ->andWhere('product.id IN (:productsId)')
                ->setParameter('productsId', $productsId);
        }

        $queryBuilder->orderBy('shop.name', $orderAsc ? 'ASC' : 'DESC');

        return $this->queryPaginationOrFail($queryBuilder);
    }

    /**
     * @param Identifier[] $groupsId
     *
     * @throws DBNotFoundException
     */
    #[\Override]
    public function findGroupsShopsOrFail(array $groupsId): PaginatorInterface
    {
        $shopEntity = Shop::class;
        $dql = <<<DQL
            SELECT shops
            FROM {$shopEntity} shops
            WHERE shops.groupId IN (:groupsId)
        DQL;

        return $this->dqlPaginationOrFail($dql, [
            'groupsId' => $groupsId,
        ]);
    }

    /**
     * @throws DBNotFoundException
     */
    #[\Override]
    public function findShopByShopNameOrFail(Identifier $groupId, NameWithSpaces $shopName, bool $orderAsc = true): PaginatorInterface
    {
        $shopEntity = Shop::class;
        $orderBy = $orderAsc ? 'ASC' : 'DESC';
        $dql = <<<DQL

        SELECT shop
        FROM {$shopEntity} shop
        WHERE shop.groupId = :groupId
            AND shop.name = :shopName
        ORDER BY shop.name {$orderBy}

        DQL;

        return $this->dqlPaginationOrFail($dql, [
            'groupId' => $groupId,
            'shopName' => $shopName,
        ]);
    }

    /**
     * @throws DBNotFoundException
     */
    #[\Override]
    public function findShopByShopNameFilterOrFail(Identifier $groupId, Filter $shopNameFilter, bool $orderAsc = true): PaginatorInterface
    {
        $shopEntity = Shop::class;
        $orderBy = $orderAsc ? 'ASC' : 'DESC';
        $dql = <<<DQL

        SELECT shop
        FROM {$shopEntity} shop
        WHERE shop.groupId = :groupId
            AND shop.name LIKE :shopNameFilter
        ORDER BY shop.name {$orderBy}

        DQL;

        return $this->dqlPaginationOrFail($dql, [
            'groupId' => $groupId,
            'shopNameFilter' => $shopNameFilter->getValueWithFilter(),
        ]);
    }

    /**
     * @throws DBNotFoundException
     */
    public function findGroupShopsFirstLetterOrFail(Identifier $groupId): array
    {
        $shopEntity = Shop::class;
        $dql = <<<DQL
            SELECT DISTINCT SUBSTRING(shop.name, 1, 1) AS firstLetter
            FROM {$shopEntity} shop
            WHERE shop.groupId = :groupId
            GROUP BY firstLetter
            ORDER BY firstLetter ASC
        DQL;

        $queryResult = $this->entityManager
            ->createQuery($dql)
            ->setParameter('groupId', $groupId)
            ->getArrayResult();

        if (empty($queryResult)) {
            throw DBNotFoundException::fromMessage('Shops not found');
        }

        return array_map(
            fn (array $row): string => mb_strtolower($row['firstLetter']),
            $queryResult
        );
    }
}
