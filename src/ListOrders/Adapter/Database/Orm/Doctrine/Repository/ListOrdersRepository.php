<?php

declare(strict_types=1);

namespace ListOrders\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBConnectionException;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBUniqueConstraintException;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;
use ListOrders\Domain\Model\ListOrders;
use ListOrders\Domain\Ports\ListOrdersRepositoryInterface;

class ListOrdersRepository extends RepositoryBase implements ListOrdersRepositoryInterface
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        private PaginatorInterface $paginator
    ) {
        parent::__construct($managerRegistry, ListOrders::class);
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
}
