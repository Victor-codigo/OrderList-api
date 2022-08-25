<?php

declare(strict_types=1);

namespace User\Repository;

use Doctrine\Persistence\ManagerRegistry;
use User\Orm\Entity\IUserEntity;
use User\Orm\Entity\Profile;

class ProfileRepository extends UserRepositoryBase
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Profile::class);
    }

    public function save(IUserEntity $profile): void
    {
        $this->objectManager->persist($profile);
        $this->objectManager->flush();
    }

    public function remove(IUserEntity $profile): void
    {
        $this->objectManager->persist($profile);
        $this->objectManager->flush();
    }
}
