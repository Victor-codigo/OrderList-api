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
    public function save(Shop $shop): void
    {
        try {
            $this->objectManager->persist($shop);
            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
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
     * @throws DBNotFoundException
     */
    public function findShopsByGroupAndNameOrFail(Identifier $groupId, NameWithSpaces $name): PaginatorInterface
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('shop')
            ->from(Shop::class, 'shop')
            ->where('shop.groupId = :groupId')
            ->setParameter('groupId', $groupId);

        if (null !== $name) {
            $queryBuilder
                ->andWhere('shop.name = :name')
                ->setParameter('name', $name);
        }

        $paginator = $this->paginator->createPaginator($queryBuilder);

        if (0 === $paginator->getItemsTotal()) {
            throw DBNotFoundException::fromMessage('Shops not found');
        }

        return $paginator;
    }

    /**
     * @param Identifier[]|null $shopsId
     * @param Identifier[]|null $productsId
     *
     * @throws DBNotFoundException
     */
    public function findShopsOrFail_old(array $shopsId = null, Identifier $groupId = null, array $productsId = null, NameWithSpaces $shopName = null, string $shopNameStartsWith = null, bool $orderAsc = true): PaginatorInterface
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('shop')
            ->from(Shop::class, 'shop');

        if (null !== $shopsId) {
            $shopsIdPlain = array_map(
                fn (Identifier $shopId) => $shopId->getValue(),
                $shopsId
            );
            $queryBuilder
                ->where('shop.id IN (:shopsId)')
                ->setParameter('shopsId', $shopsIdPlain);
        }

        if (null !== $groupId) {
            $queryBuilder
                ->andWhere('shop.groupId = :groupId')
                ->setParameter('groupId', $groupId);
        }

        if (null !== $productsId) {
            $queryBuilder
                ->leftJoin(ProductShop::class, 'productShop', Join::WITH, 'shop.id = productShop.shopId')
                ->leftJoin(Product::class, 'product', Join::WITH, 'productShop.productId = product.id')
                ->andWhere('product.id IN (:productsId)')
                ->setParameter('productsId', $productsId);
        }

        if (null !== $shopNameStartsWith && (null === $shopName || $shopName->isNull())) {
            $queryBuilder
                ->andWhere('shop.name LIKE :shopStartsWith')
                ->setParameter('shopStartsWith', "{$shopNameStartsWith}%");
        } elseif (null !== $shopName && !$shopName->isNull()) {
            $queryBuilder
                ->andWhere('shop.name = :shopName')
                ->setParameter('shopName', $shopName);
        }

        if ($orderAsc) {
            $queryBuilder->addOrderBy('shop.name', 'ASC');
        } else {
            $queryBuilder->addOrderBy('shop.name', 'DESC');
        }

        return $this->queryPaginationOrFail($queryBuilder);
    }

    /**
     * @param Identifier[]|null $shopsId
     * @param Identifier[]|null $productsId
     *
     * @throws DBNotFoundException
     */
    public function findShopsOrFail(
        array $shopsId = null,
        Identifier $groupId = null,
        array $productsId = null,
        NameWithSpaces $shopName = null,
        Filter $shopFilter = null,
        bool $orderAsc = true
    ): PaginatorInterface {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('shop')
            ->from(Shop::class, 'shop');

        if (null !== $shopsId) {
            $shopsIdPlain = array_map(
                fn (Identifier $shopId) => $shopId->getValue(),
                $shopsId
            );
            $queryBuilder
                ->where('shop.id IN (:shopsId)')
                ->setParameter('shopsId', $shopsIdPlain);
        }

        if (null !== $groupId) {
            $queryBuilder
                ->andWhere('shop.groupId = :groupId')
                ->setParameter('groupId', $groupId);
        }

        if (null !== $productsId) {
            $queryBuilder
                ->leftJoin(ProductShop::class, 'productShop', Join::WITH, 'shop.id = productShop.shopId')
                ->leftJoin(Product::class, 'product', Join::WITH, 'productShop.productId = product.id')
                ->andWhere('product.id IN (:productsId)')
                ->setParameter('productsId', $productsId);
        }

        if (null !== $shopFilter && !$shopFilter->isNull() && (null === $shopName || $shopName->isNull())) {
            $queryBuilder
                ->andWhere('shop.name LIKE :shopNameFilter')
                ->setParameter('shopNameFilter', $shopFilter->getValueWithFilter());
        } elseif (null !== $shopName && !$shopName->isNull()) {
            $queryBuilder
                ->andWhere('shop.name = :shopName')
                ->setParameter('shopName', $shopName);
        }

        if ($orderAsc) {
            $queryBuilder->addOrderBy('shop.name', 'ASC');
        } else {
            $queryBuilder->addOrderBy('shop.name', 'DESC');
        }

        return $this->queryPaginationOrFail($queryBuilder);
    }
}
