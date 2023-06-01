<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

abstract class RepositoryBase extends ServiceEntityRepository
{
    protected ObjectManager $objectManager;
    protected EntityManagerInterface $entityManager;
    protected PaginatorInterface|null $paginator;

    public function __construct(ManagerRegistry $managerRegistry, string $entityClass, PaginatorInterface|null $paginator = null)
    {
        parent::__construct($managerRegistry, $entityClass);

        $this->objectManager = $managerRegistry->getManager();
        $this->entityManager = $this->getEntityManager();
        $this->paginator = $paginator;
    }

    public function generateId(): string
    {
        return Uuid::v4()->toRfc4122();
    }

    public function isValidUuid(string $id)
    {
        return Uuid::isValid($id);
    }

    protected function getClassUnqualifiedName(string $qualifiedName): string
    {
        $qualifiedNameArray = explode('\\', $qualifiedName);

        return end($qualifiedNameArray);
    }

    protected function getString(string|int $constant): string
    {
        return (string) $constant;
    }

    /**
     * @throws DBNotFoundException
     */
    protected function dqlPaginationOrFail(string $dql, array $parameters): PaginatorInterface
    {
        $query = $this->entityManager
            ->createQuery($dql)
            ->setParameters($parameters);

        $pagination = $this->paginator->createPaginator($query);

        if (0 === $pagination->getItemsTotal()) {
            throw DBNotFoundException::fromMessage('Not found');
        }

        return $pagination;
    }
}
