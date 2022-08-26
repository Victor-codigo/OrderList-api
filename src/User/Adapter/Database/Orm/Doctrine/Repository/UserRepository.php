<?php

declare(strict_types=1);

namespace User\Adapter\Database\Orm\Doctrine\Repository;

use Doctrine\Persistence\ManagerRegistry;
use User\Adapter\Database\Orm\Doctrine\Entity\User;
use User\Domain\Model\EntityBase as EntityBaseDomain;

final class UserRepository extends UserRepositoryBase
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, User::class);
    }

    public function save(EntityBaseDomain $user): void
    {
        $doctrineUser = User::createFromDomain($user);

        $this->objectManager->persist($doctrineUser);
        $this->objectManager->flush();
    }

    public function remove(EntityBaseDomain $user): void
    {
        $doctrineUser = User::createFromDomain($user);

        $this->objectManager->remove($doctrineUser);
        $this->objectManager->flush();
    }
}
