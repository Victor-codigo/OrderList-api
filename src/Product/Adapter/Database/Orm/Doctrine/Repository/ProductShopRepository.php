<?php

declare(strict_types=1);

namespace Product\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\EntityIdentityCollisionException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductShopRepositoryInterface;

class ProductShopRepository extends RepositoryBase implements ProductShopRepositoryInterface
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        PaginatorInterface $paginator
    ) {
        parent::__construct($managerRegistry, ProductShop::class, $paginator);
    }

    /**
     * @param ProductShop[] $productsShops
     *
     * @throws DBConnectionException
     * @throws DBUniqueConstraintException
     */
    #[\Override]
    public function save(array $productsShops): void
    {
        try {
            foreach ($productsShops as $productShop) {
                $this->objectManager->persist($productShop);
            }

            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException|EntityIdentityCollisionException $e) {
            throw DBUniqueConstraintException::fromId($productShop->getId(), $e->getCode());
        } catch (\Throwable $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param ProductShop[] $productsShops
     *
     * @throws DBConnectionException
     */
    #[\Override]
    public function remove(array $productsShops): void
    {
        try {
            foreach ($productsShops as $productShop) {
                $this->objectManager->remove($productShop);
            }

            $this->objectManager->flush();
        } catch (\Throwable $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @throws DBNotFoundException
     */
    #[\Override]
    public function findProductsAndShopsOrFail(?array $productsId = null, ?array $shopsId = null, ?Identifier $groupId = null): PaginatorInterface
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('productShops')
            ->from(ProductShop::class, 'productShops');

        if (null !== $groupId) {
            $query
                ->leftJoin(Product::class, 'product', Join::WITH, 'productShops.productId = product.id')
                ->where('product.groupId = :groupId')
                ->setParameter('groupId', $groupId);
        }

        if (!empty($productsId)) {
            $query
                ->andWhere('productShops.productId IN (:productsId)')
                ->setParameter('productsId', $productsId);
        }

        if (!empty($shopsId)) {
            $query
                ->andWhere('productShops.shopId IN (:shopsId)')
                ->setParameter('shopsId', $shopsId);
        }

        $paginator = $this->paginator->createPaginator($query);

        if (0 === $paginator->getItemsTotal()) {
            throw DBNotFoundException::fromMessage('Product or shop not found');
        }

        return $paginator;
    }
}
