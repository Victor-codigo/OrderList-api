<?php

declare(strict_types=1);

namespace Common\Adapter\Database\Orm\Doctrine\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

abstract class RepositoryBase
{
    protected ServiceEntityRepository $serviceEntityRepository;
    protected ManagerRegistry $managerRegistry;
    protected ObjectManager $objectManager;

    public function __construct(ManagerRegistry $managerRegistry, string $entity)
    {
        $this->managerRegistry = $managerRegistry;
        $this->serviceEntityRepository = new ServiceEntityRepository($this->managerRegistry, $entity);
        $this->objectManager = $this->managerRegistry->getManager();
    }

    public function generateId(): string
    {
        return Uuid::v4()->toRfc4122();
    }
}
