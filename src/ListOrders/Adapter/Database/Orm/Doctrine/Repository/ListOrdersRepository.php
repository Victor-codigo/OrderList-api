<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;

class ListOrdersRepository extends RepositoryBase implements ListOrdersRepositoryInterface
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        PaginatorInterface $paginator
    ) {
        parent::__construct($managerRegistry, ListOrders::class, $paginator);
    }

    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(ListOrders $listOrders): void
    {
        try {
            $this->objectManager->persist($listOrders);

            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DBUniqueConstraintException::fromId($listOrders->getId()->getValue(), $e->getCode());
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @throws DBConnectionException
     */
    public function remove(ListOrders $listOrders): void
    {
        try {
            $this->objectManager->remove($listOrders);

            $this->objectManager->flush();
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param Identifier[] $ListsOrdersId
     *
     * @throws DBNotFoundException
     */
    public function findListOrderByIdOrFail(array $ListsOrdersId, Identifier|null $groupId = null): PaginatorInterface
    {
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('listOrders')
            ->from(ListOrders::class, 'listOrders')
            ->where('listOrders.id IN (:listOrdersId)')
            ->setParameter('listOrdersId', $ListsOrdersId);

        if (null !== $groupId) {
            $query
                ->andWhere('listOrders.groupId = :groupId')
                ->setParameter('groupId', $groupId);
        }

        return $this->queryPaginationOrFail($query);
    }

    /**
     * @throws DBNotFoundException
     */
    public function findListOrderByNameStarsWithOrFail(NameWithSpaces $listsOrdersNameStarsWith, Identifier|null $groupId = null): PaginatorInterface
    {
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('listOrders')
            ->from(ListOrders::class, 'listOrders')
            ->where('listOrders.name LIKE :listOrdersNameStartsWith')
            ->setParameter('listOrdersNameStartsWith', $listsOrdersNameStarsWith.'%');

        if (null !== $groupId) {
            $query
                ->andWhere('listOrders.groupId = :groupId')
                ->setParameter('groupId', $groupId);
        }

        return $this->queryPaginationOrFail($query);
    }
}
