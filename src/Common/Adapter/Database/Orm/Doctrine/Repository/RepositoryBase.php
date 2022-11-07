<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

abstract class RepositoryBase extends ServiceEntityRepository
{
    protected ObjectManager $objectManager;
    protected EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $managerRegistry, string $entityClass)
    {
        parent::__construct($managerRegistry, $entityClass);

        $this->objectManager = $managerRegistry->getManager();
        $this->entityManager = $this->getEntityManager();
    }

    public function generateId(): string
    {
        return Uuid::v4()->toRfc4122();
    }

    public function isValidUuid(string $id)
    {
        return Uuid::isValid($id);
    }
}
