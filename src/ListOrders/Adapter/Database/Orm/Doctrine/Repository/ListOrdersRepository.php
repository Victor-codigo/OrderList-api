<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;
use Order\Domain\Model\Order;
use Product\Domain\Model\Product;
use Shop\Domain\Model\Shop;

class ListOrdersRepository extends RepositoryBase implements ListOrdersRepositoryInterface
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        PaginatorInterface $paginator
    ) {
        parent::__construct($managerRegistry, ListOrders::class, $paginator);
    }

    /**
     * @param ListOrders[] $listsOrders
     *
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(array $listsOrders): void
    {
        try {
            foreach ($listsOrders as $listOrders) {
                $this->objectManager->persist($listOrders);
            }

            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DBUniqueConstraintException::fromId($listOrders->getId()->getValue(), $e->getCode());
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function saveListOrdersAndOrders(ListOrders $listOrders): void
    {
        try {
            $this->objectManager->persist($listOrders);

            foreach ($listOrders->getOrders() as $order) {
                $this->objectManager->persist($order);
            }

            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DBUniqueConstraintException::fromId($listOrders->getId()->getValue(), $e->getCode());
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param ListOrders[] $listsOrders
     *
     * @throws DBConnectionException
     */
    public function remove(array $listsOrders): void
    {
        try {
            foreach ($listsOrders as $listsOrders) {
                $this->objectManager->remove($listsOrders);
            }

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
    public function findListOrderByIdOrFail(array $ListsOrdersId, ?Identifier $groupId = null): PaginatorInterface
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
    public function findListOrdersGroup(Identifier $groupId, bool $orderAsc): PaginatorInterface
    {
        $listOrdersEntity = ListOrders::class;
        $orderBy = $orderAsc ? 'ASC' : 'DESC';
        $dql = <<<DQL
            SELECT listOrders
            FROM {$listOrdersEntity} listOrders
            WHERE listOrders.groupId = :groupId
            ORDER BY listOrders.name {$orderBy}
        DQL;

        return $this->dqlPaginationOrFail($dql, [
            'groupId' => $groupId,
        ]);
    }

    /**
     * @throws DBNotFoundException
     */
    public function findListOrderByListOrdersNameFilterOrFail(Identifier $groupId, Filter $filterText, bool $orderAsc): PaginatorInterface
    {
        $listOrdersEntity = ListOrders::class;
        $orderBy = $orderAsc ? 'ASC' : 'DESC';
        $dql = <<<DQL
            SELECT listOrders
            FROM {$listOrdersEntity} listOrders
            WHERE listOrders.groupId = :groupId
                AND listOrders.name LIKE :filterTextValue
            ORDER BY listOrders.name {$orderBy}
        DQL;

        return $this->dqlPaginationOrFail($dql, [
            'groupId' => $groupId,
            'filterTextValue' => $filterText->getValueWithFilter(),
        ]);
    }

    /**
     * @throws DBNotFoundException
     */
    public function findListOrderByProductNameFilterOrFail(Identifier $groupId, Filter $filterText, bool $orderAsc): PaginatorInterface
    {
        $listOrdersEntity = ListOrders::class;
        $orderEntity = Order::class;
        $productEntity = Product::class;
        $orderBy = $orderAsc ? 'ASC' : 'DESC';
        $dql = <<<DQL
            SELECT listOrders
            FROM {$listOrdersEntity} listOrders
                LEFT JOIN {$orderEntity} orderEntity WITH listOrders.id = orderEntity.listOrdersId
                LEFT JOIN {$productEntity} product WITH orderEntity.productId = product.id
            WHERE listOrders.groupId = :groupId
                AND product.name LIKE :filterTextValue
            ORDER BY listOrders.name {$orderBy}
        DQL;

        return $this->dqlPaginationOrFail($dql, [
            'groupId' => $groupId,
            'filterTextValue' => $filterText->getValueWithFilter(),
        ]);
    }

    /**
     * @throws DBNotFoundException
     */
    public function findListOrderByShopNameFilterOrFail(Identifier $groupId, Filter $filterText, bool $orderAsc): PaginatorInterface
    {
        $listOrdersEntity = ListOrders::class;
        $orderEntity = Order::class;
        $shopEntity = Shop::class;
        $orderBy = $orderAsc ? 'ASC' : 'DESC';
        $dql = <<<DQL
            SELECT listOrders
            FROM {$listOrdersEntity} listOrders
                LEFT JOIN {$orderEntity} orderEntity WITH listOrders.id = orderEntity.listOrdersId
                LEFT JOIN {$shopEntity} shop WITH orderEntity.shopId = shop.id
            WHERE listOrders.groupId = :groupId
                AND shop.name LIKE :filterTextValue
            ORDER BY listOrders.name {$orderBy}
        DQL;

        return $this->dqlPaginationOrFail($dql, [
            'groupId' => $groupId,
            'filterTextValue' => $filterText->getValueWithFilter(),
        ]);
    }

    /**
     * @param Identifier[] $groupsId
     *
     * @throws DBNotFoundException
     */
    public function findGroupsListsOrdersOrFail(array $groupsId): PaginatorInterface
    {
        $listOrdersEntity = ListOrders::class;
        $dql = <<<DQL
            SELECT listOrders
            FROM {$listOrdersEntity} listOrders
            WHERE listOrders.groupId IN (:groupsId)
        DQL;

        return $this->dqlPaginationOrFail($dql, [
            'groupsId' => $groupsId,
        ]);
    }

    /**
     * @throws DBNotFoundException
     */
    public function findGroupListOrdersFirstLetterOrFail(Identifier $groupId): array
    {
        $listOrdersEntity = ListOrders::class;
        $dql = <<<DQL
            SELECT DISTINCT SUBSTRING(listOrders.name, 1, 1) AS firstLetter
            FROM {$listOrdersEntity} listOrders
            WHERE listOrders.groupId = :groupId
            GROUP BY firstLetter
            ORDER BY firstLetter ASC
        DQL;

        $queryResult = $this->entityManager
            ->createQuery($dql)
            ->setParameter('groupId', $groupId)
            ->getArrayResult();

        if (empty($queryResult)) {
            throw DBNotFoundException::fromMessage('listOrders not found');
        }

        return array_map(
            fn (array $row): string => mb_strtolower($row['firstLetter']),
            $queryResult
        );
    }
}
