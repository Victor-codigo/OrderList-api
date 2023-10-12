<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Model\ListOrdersOrders;
use ListOrders\Domain\Ports\ListOrdersOrdersRepositoryInterface;
use Order\Domain\Model\Order;

class ListOrdersOrdersRepository extends RepositoryBase implements ListOrdersOrdersRepositoryInterface
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        PaginatorInterface $paginator
    ) {
        parent::__construct($managerRegistry, ListOrdersOrders::class, $paginator);
    }

    /**
     * @param ListOrdersOrders[] $listOrdersOrders
     *
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(array $listOrdersOrders): void
    {
        try {
            foreach ($listOrdersOrders as $listOrdersOrder) {
                $this->objectManager->persist($listOrdersOrder);
            }

            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DBUniqueConstraintException::fromId($listOrdersOrder->getId()->getValue(), $e->getCode());
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param ListOrdersOrders[] $listOrdersOrders
     *
     * @throws DBConnectionException
     */
    public function remove(array $listOrdersOrders): void
    {
        try {
            foreach ($listOrdersOrders as $listOrdersOrder) {
                $this->objectManager->remove($listOrdersOrder);
            }

            $this->objectManager->flush();
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param Identifier $ordersId
     *
     * @throws DBNotFoundException
     */
    public function findListOrderOrdersByIdOrFail(Identifier $listOrdersId, Identifier $groupId, array $ordersId = []): PaginatorInterface
    {
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('listOrdersOrders')
            ->from(ListOrdersOrders::class, 'listOrdersOrders')
            ->leftJoin(ListOrders::class, 'listOrders', Join::WITH, 'listOrdersOrders.listOrdersId = listOrders.id')
            ->where('listOrders.groupId = :groupId')
            ->andWhere('listOrdersOrders.listOrdersId = :listOrdersId')
            ->setParameter('listOrdersId', $listOrdersId)
            ->setParameter('groupId', $groupId);

        if (!empty($ordersId)) {
            $query
                ->andWhere('listOrdersOrders.orderId In (:ordersId)')
                ->setParameter('ordersId', $ordersId);
        }

        return $this->queryPaginationOrFail($query);
    }

    /**
     * @throws DBNotFoundException
     */
    public function findListOrderOrdersDataByIdOrFail(Identifier $listOrdersId, Identifier $groupId): PaginatorInterface
    {
        $ordersEntity = Order::class;
        $listOrdersOrdersEntity = ListOrdersOrders::class;
        $dql = <<<DQL
            SELECT orders
            FROM {$ordersEntity} orders
                LEFT JOIN {$listOrdersOrdersEntity} listOrdersOrders WITH orders.id = listOrdersOrders.orderId
            WHERE listOrdersOrders.listOrdersId = :listOrdersId
                AND orders.groupId = :groupId
        DQL;

        return $this->queryPaginationOrFail(
            $this->entityManager->createQuery($dql),
            [
                'listOrdersId' => $listOrdersId,
                'groupId' => $groupId,
            ]
        );
    }
}
