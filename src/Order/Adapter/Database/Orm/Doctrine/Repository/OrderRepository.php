<?php

declare(strict_types=1);

namespace Order\Adapter\Database\Orm\Doctrine\Repository;

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
use ListOrders\Domain\Model\ListOrders;
use Order\Domain\Model\Order;
use Order\Domain\Ports\Repository\OrderRepositoryInterface;
use Product\Domain\Model\Product;
use Shop\Domain\Model\Shop;

class OrderRepository extends RepositoryBase implements OrderRepositoryInterface
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        PaginatorInterface $paginator
    ) {
        parent::__construct($managerRegistry, Order::class, $paginator);
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
    public function findOrdersByIdOrFail(Identifier $groupId, array $ordersId, bool $orderAsc): PaginatorInterface
    {
        $orderEntity = Order::class;
        $productEntity = Product::class;
        $orderBy = $orderAsc ? 'ASC' : 'DESC';
        $dql = <<<DQL
            SELECT orders
            FROM {$orderEntity} orders
                LEFT JOIN {$productEntity} products WITH orders.productId = products.id
            WHERE orders.groupId = :groupId
                AND orders.id IN (:ordersId)
            ORDER BY products.name {$orderBy}
        DQL;

        return $this->dqlPaginationOrFail($dql, [
            'groupId' => $groupId,
            'ordersId' => $ordersId,
        ]);
    }

    /**
     * @throws DBNotFoundException
     */
    public function findOrdersByListOrdersNameOrFail(Identifier $groupId, NameWithSpaces $listOrderName, bool $orderAsc): PaginatorInterface
    {
        $ordersEntity = Order::class;
        $listOrdersEntity = ListOrders::class;
        $productEntity = Product::class;
        $orderBy = $orderAsc ? 'ASC' : 'DESC';
        $dql = <<<DQL
            SELECT orders
            FROM {$ordersEntity} orders
                LEFT JOIN {$listOrdersEntity} listOrders WITH orders.listOrdersId = listOrders.id
                LEFT JOIN {$productEntity} products WITH orders.productId = products.id
            WHERE orders.groupId = :groupId
                AND listOrders.name = :filterTextValue
            ORDER BY products.name {$orderBy}
        DQL;

        return $this->dqlPaginationOrFail($dql, [
            'groupId' => $groupId,
            'filterTextValue' => $listOrderName,
        ]);
    }

    /**
     * @throws DBNotFoundException
     */
    public function findOrdersByProductNameFilterOrFail(Identifier $groupId, ?Identifier $listOrdersId, Filter $filterText, bool $orderAsc): PaginatorInterface
    {
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('orders')
            ->from(Order::class, 'orders')
            ->leftJoin(Product::class, 'products', Join::WITH, 'orders.productId = products.id')
            ->where('orders.groupId = :groupId');

        if (null !== $listOrdersId && !$listOrdersId->isNull()) {
            $query
                ->andWhere('orders.listOrdersId = :listOrdersId')
                ->setParameter('listOrdersId', $listOrdersId);
        }

        $query
            ->andWhere('products.name LIKE :filterTextValue')
            ->orderBy('products.name', $orderAsc ? 'ASC' : 'DESC')
            ->setParameter('groupId', $groupId)
            ->setParameter('filterTextValue', $filterText->getValueWithFilter());

        return $this->queryPaginationOrFail($query);
    }

    /**
     * @throws DBNotFoundException
     */
    public function findOrdersByShopNameFilterOrFail(Identifier $groupId, ?Identifier $listOrdersId, Filter $filterText, bool $orderAsc): PaginatorInterface
    {
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('orders')
            ->from(Order::class, 'orders')
            ->leftJoin(Shop::class, 'shops', Join::WITH, 'orders.shopId = shops.id')
            ->where('orders.groupId = :groupId')
            ->andWhere('shops.name LIKE :filterTextValue');

        if (null !== $listOrdersId && !$listOrdersId->isNull()) {
            $query
                ->andWhere('orders.listOrdersId = :listOrdersId')
                ->setParameter('listOrdersId', $listOrdersId);
        }

        $query
            ->orderBy('shops.name', $orderAsc ? 'ASC' : 'DESC')
            ->setParameter('groupId', $groupId)
            ->setParameter('filterTextValue', $filterText->getValueWithFilter());

        return $this->queryPaginationOrFail($query);
    }

    /**
     * @throws DBNotFoundException
     */
    public function findOrdersByGroupIdOrFail(Identifier $groupId, bool $orderAsc): PaginatorInterface
    {
        $ordersEntity = Order::class;
        $productEntity = Product::class;
        $orderBy = $orderAsc ? 'ASC' : 'DESC';
        $dql = <<<DQL
            SELECT orders
            FROM {$ordersEntity} orders
                LEFT JOIN {$productEntity} products WITH orders.productId = products.id
            WHERE orders.groupId = :groupId
            ORDER BY products.name {$orderBy}
        DQL;

        return $this->queryPaginationOrFail(
            $this->entityManager->createQuery($dql), [
                'groupId' => $groupId,
            ]
        );
    }

    /**
     * @param Identifier[] $productsId
     * @param Identifier[] $shopsId
     *
     * @throws DBNotFoundException
     */
    public function findOrdersByListOrdersIdProductIdAndShopIdOrFail(Identifier $groupId, Identifier $listOrdersId, array $productsId, array $shopsId): PaginatorInterface
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('orders')
            ->from(Order::class, 'orders')
            ->where('orders.groupId = :groupId')
            ->andWhere('orders.listOrdersId = :listOrdersId')
            ->andWhere('orders.productId IN (:productsId)')
            ->setParameter('groupId', $groupId)
            ->setParameter('listOrdersId', $listOrdersId)
            ->setParameter('productsId', $productsId);

        if (!empty($shopsId)) {
            $query
                ->andWhere('orders.shopId IN (:shopsId)')
                ->setParameter('shopsId', $shopsId);
        } else {
            $query->andWhere('orders.shopId IS NULL');
        }

        return $this->queryPaginationOrFail($query);
    }

    /**
     * @throws DBNotFoundException
     */
    public function findOrdersByListOrdersIdOrFail(Identifier $listOrderId, Identifier $groupId, bool $orderAsc): PaginatorInterface
    {
        $ordersEntity = Order::class;
        $listOrdersEntity = ListOrders::class;
        $productEntity = Product::class;
        $orderBy = $orderAsc ? 'ASC' : 'DESC';
        $dql = <<<DQL
            SELECT orders
            FROM {$ordersEntity} orders
                LEFT JOIN {$listOrdersEntity} listOrders WITH orders.listOrdersId = listOrders.id
                LEFT JOIN {$productEntity} products WITH products.id = orders.productId
            WHERE orders.groupId = :groupId
                AND listOrders.id = :listOrderId
            ORDER BY products.name {$orderBy}
        DQL;

        return $this->dqlPaginationOrFail($dql, [
            'groupId' => $groupId,
            'listOrderId' => $listOrderId,
        ]);
    }
}
