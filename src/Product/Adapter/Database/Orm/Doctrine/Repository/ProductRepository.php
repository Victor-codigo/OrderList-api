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
use Doctrine\Persistence\ManagerRegistry;
use Product\Domain\Model\Product;
use Product\Domain\Port\Repository\ProductRepositoryInterface;

class ProductRepository extends RepositoryBase implements ProductRepositoryInterface
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        private PaginatorInterface $paginator
    ) {
        parent::__construct($managerRegistry, Product::class);
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
     * @throws DBConnectionException
     */
    public function remove(Product $product): void
    {
        try {
            $this->objectManager->remove($product);
            $this->objectManager->flush();
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @throws DBNotFoundException
     */
    public function findProductsByGroupAndNameOrFail(Identifier $groupId, NameWithSpaces|null $name = null): PaginatorInterface
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
}
