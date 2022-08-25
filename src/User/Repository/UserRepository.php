<?php

declare(strict_types=1);

namespace User\Repository;

use Doctrine\Persistence\ManagerRegistry;
use User\Orm\Entity\IUserEntity;
use User\Orm\Entity\User;

final class UserRepository extends UserRepositoryBase
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, User::class);
    }

    public function findById(string $id): User|null
    {
        return $this->objectRepository->findOneBy(['id' => $id]);
    }

    public function save(IUserEntity $user): void
    {
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    public function remove(IUserEntity $user): void
    {
        $this->objectManager->remove($user);
        $this->objectManager->flush();
    }
}
