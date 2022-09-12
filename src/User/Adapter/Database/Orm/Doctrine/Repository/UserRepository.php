<?php

declare(strict_types=1);

namespace User\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Doctrine\Persistence\ManagerRegistry;
use User\Domain\Model\User;
use User\Domain\Repository\IUserRepository;

final class UserRepository extends RepositoryBase implements IUserRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, User::class);
    }

    public function save(User $user): void
    {
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    public function remove(User $user): void
    {
        $this->objectManager->remove($user);
        $this->objectManager->flush();
    }
}
