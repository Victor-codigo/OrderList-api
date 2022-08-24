<?php

declare(strict_types=1);

namespace User\Repository;

use App\Orm\Entity\IEntity;
use App\Orm\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends DoctrineRepositoryBase
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, User::class);
    }

    public function findById(string $id): User|null
    {
        return $this->objectRepository->findOneBy(['id' => $id]);
    }

    public function save(IEntity $user): void
    {
        $this->entitySave($user);
    }

    public function remove(IEntity $user): void
    {
        $this->entityRemove($user);
    }
}
