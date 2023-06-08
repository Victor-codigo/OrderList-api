<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Model\ListOrdersOrders;
use ListOrders\Domain\Ports\ListOrdersOrdersRepositoryInterface;

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
     * @throws DBNotFoundException
     */
    public function findListOrderOrdersByIdOrFail(Identifier $listOrdersId, Identifier $groupId): PaginatorInterface
    {
        $listOrdersEntity = ListOrders::class;
        $listOrdersOrdersEntity = ListOrdersOrders::class;
        $dql = <<<DQL
            SELECT listOrdersOrders
            FROM {$listOrdersOrdersEntity} listOrdersOrders
                LEFT JOIN {$listOrdersEntity} listOrders WITH listOrdersOrders.listOrdersId = listOrders.id
            WHERE listOrders.groupId = :groupId
                AND listOrdersOrders.listOrdersId = :listOrdersId
        DQL;

        return $this->dqlPaginationOrFail($dql, [
            'groupId' => $groupId,
            'listOrdersId' => $listOrdersId,
        ]);
    }
}
