<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Repository;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

/**
 * @phpstan-template TEntityClass of object
 *
 * @phpstan-extends ServiceEntityRepository<TEntityClass>
 */
abstract class RepositoryBase extends ServiceEntityRepository
{
    protected ObjectManager $objectManager;
    protected EntityManagerInterface $entityManager;
    /**
     * @var PaginatorInterface<int, object>|null
     */
    protected ?PaginatorInterface $paginator;

    /**
     * @param class-string                    $entityClass<T>
     * @param PaginatorInterface<int, object> $paginator
     */
    public function __construct(ManagerRegistry $managerRegistry, string $entityClass, ?PaginatorInterface $paginator = null)
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

    public function isValidUuid(string $id): bool
    {
        return Uuid::isValid($id);
    }

    /**
     * @param mixed[] $parameters
     *
     * @return PaginatorInterface<int, mixed>
     *
     * @throws DBNotFoundException
     */
    protected function queryPaginationOrFail(Query|QueryBuilder $query, array $parameters = []): PaginatorInterface
    {
        if (!empty($parameters)) {
            $query->setParameters($parameters);
        }

        $pagination = $this->paginator->createPaginator($query);

        if (0 === $pagination->getItemsTotal()) {
            throw DBNotFoundException::fromMessage('Not found');
        }

        return $pagination;
    }

    /**
     * @param mixed[] $parameters
     *
     * @return PaginatorInterface<int, mixed>
     *
     * @throws DBNotFoundException
     */
    protected function dqlPaginationOrFail(string $dql, array $parameters = []): PaginatorInterface
    {
        $query = $this->entityManager->createQuery($dql);

        return $this->queryPaginationOrFail($query, $parameters);
    }
}
