<?php

declare(strict_types=1);

namespace User\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

abstract class UserRepositoryBase implements IUserRepository
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
}
