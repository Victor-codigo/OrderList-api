<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

abstract class RepositoryBase extends ServiceEntityRepository
{
    protected ObjectManager $objectManager;

    public function __construct(ManagerRegistry $managerRegistry, string $entity)
    {
        parent::__construct($managerRegistry, $entity);

        $this->objectManager = $managerRegistry->getManager();
    }

    public function generateId(): string
    {
        return Uuid::v4()->toRfc4122();
    }
}
