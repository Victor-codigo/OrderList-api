<?php

declare(strict_types=1);

namespace Product\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Product\Domain\Model\Product;
use Product\Domain\Model\ProductShop;
use Product\Domain\Port\Repository\ProductRepositoryInterface;
use Shop\Domain\Model\Shop;

class ProductRepository extends RepositoryBase implements ProductRepositoryInterface
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        PaginatorInterface $paginator
    ) {
        parent::__construct($managerRegistry, Product::class, $paginator);
    }

    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(Product $product): void
    {
        try {
            $this->objectManager->persist($product);
            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DBUniqueConstraintException::fromId($product->getId()->getValue(), $e->getCode());
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param Product[] $products
     *
     * @throws DBConnectionException
     */
    public function remove(array $products): void
    {
        try {
            foreach ($products as $product) {
                $this->objectManager->remove($product);
            }

            $this->objectManager->flush();
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @throws DBNotFoundException
     */
    public function findProductsByGroupAndNameOrFail(Identifier $groupId, NameWithSpaces $name): PaginatorInterface
    {
        $queryBuilder = $this->entityManager
            ->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('product.groupId = :groupId')
            ->setParameter('groupId', $groupId);

        if (null !== $name) {
            $queryBuilder
                ->andWhere('product.name = :name')
                ->setParameter('name', $name);
        }

        $paginator = $this->paginator->createPaginator($queryBuilder);

        if (0 === $paginator->getItemsTotal()) {
            throw DBNotFoundException::fromMessage('Products not found');
        }

        return $paginator;
    }

    /**
     * @param Identifier[]|null $productsId
     * @param Identifier[]|null $shopsId
     *
     * @throws DBNotFoundException
     */
    public function findProductsOrFail(array|null $productsId = null, Identifier|null $groupId = null, array|null $shopsId = null, string|null $productNameStartsWith = null): PaginatorInterface
    {
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product');

        if (null !== $productsId) {
            $query
                ->where('product.id IN (:productId)')
                ->setParameter('productId', $productsId);
        }

        if (null !== $groupId) {
            $query
                ->andWhere('product.groupId = :groupId')
                ->setParameter('groupId', $groupId);
        }

        if (null !== $shopsId) {
            $query
                ->leftJoin(ProductShop::class, 'productShop', Join::WITH, 'product.id = productShop.productId')
                ->leftJoin(Shop::class, 'shop', Join::WITH, 'productShop.shopId = shop.id')
                ->andWhere('shop.id IN (:shopId)')
                ->setParameter('shopId', $shopsId);
        }

        if (null !== $productNameStartsWith) {
            $query
                ->andWhere('product.name LIKE :productStartsWith')
                ->setParameter('productStartsWith', "{$productNameStartsWith}%");
        }

        $paginator = $this->paginator->createPaginator($query);

        if (0 === $paginator->getItemsTotal()) {
            throw DBNotFoundException::fromMessage('Products not found');
        }

        return $paginator;
    }
}
