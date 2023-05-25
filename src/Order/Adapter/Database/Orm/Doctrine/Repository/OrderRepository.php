<?php

declare(strict_types=1);

namespace Order\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;

class OrderRepository extends RepositoryBase implements OrderRepositoryInterface
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        private PaginatorInterface $paginator
    ) {
        parent::__construct($managerRegistry, Order::class);
    }

    /**
     * @param Order[] $orders
     *
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(array $orders): void
    {
        try {
            foreach ($orders as $order) {
                $this->objectManager->persist($order);
            }

            $this->objectManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw DBUniqueConstraintException::fromId($order->getId()->getValue(), $e->getCode());
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param Order[] $orders
     *
     * @throws DBConnectionException
     */
    public function remove(array $orders): void
    {
        try {
            foreach ($orders as $order) {
                $this->objectManager->remove($order);
            }

            $this->objectManager->flush();
        } catch (\Exception $e) {
            throw DBConnectionException::fromConnection($e->getCode());
        }
    }

    /**
     * @param Identifier[] $ordersId
     *
     * @throws DBNotFoundException
     */
    public function findOrdersByIdOrFail(array $ordersId, Identifier|null $groupId = null): PaginatorInterface
    {
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('orders')
            ->from(Order::class, 'orders')
            ->where('orders.id IN (:ordersId)')
            ->setParameter('ordersId', $ordersId);

        if (null !== $groupId) {
            $query
                ->andWhere('orders.groupId = :groupId')
                ->setParameter('groupId', $groupId);
        }

        $paginator = $this->paginator->createPaginator($query);

        if (0 === $paginator->getItemsTotal()) {
            throw DBNotFoundException::fromMessage('No orders found');
        }

        return $paginator;
    }
}
