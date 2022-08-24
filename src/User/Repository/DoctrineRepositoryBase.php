<?php

declare(strict_types=1);

namespace User\Repository;

use App\Orm\Entity\IEntity;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

abstract class DoctrineRepositoryBase
{
    protected ManagerRegistry $managerRegistry;
    protected Connection $connection;
    protected ObjectRepository $objectRepository;

    public function __construct(ManagerRegistry $managerRegistry, string $entity)
    {
        $this->managerRegistry = $managerRegistry;
        $this->connection = $this->managerRegistry->getConnection();
        $this->objectRepository = $this->getEntityManager()->getRepository($entity);
    }

    protected function getEntityManager(): EntityManager|ObjectManager
    {
        $entityManager = $this->managerRegistry->getManager();

        if ($entityManager->isOpen()) {
            return $entityManager;
        }

        return $this->managerRegistry->resetManager();
    }

    protected function entitySave(IEntity $entity): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        $entityManager->flush();
    }

    protected function entityRemove(IEntity $entity): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->remove($entity);
        $entityManager->flush();
    }
}
