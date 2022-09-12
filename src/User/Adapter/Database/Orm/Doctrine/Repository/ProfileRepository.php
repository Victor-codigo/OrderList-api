<?php

declare(strict_types=1);

namespace User\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Doctrine\Persistence\ManagerRegistry;
use User\Adapter\Database\Orm\Doctrine\Entity\Profile;
use User\Domain\Model\EntityBase as EntityBaseDomain;

class ProfileRepository extends RepositoryBase
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Profile::class);
    }

    public function save(EntityBaseDomain $profile): void
    {
        $this->objectManager->persist($profile);
        $this->objectManager->flush();
    }

    public function remove(EntityBaseDomain $profile): void
    {
        $this->objectManager->persist($profile);
        $this->objectManager->flush();
    }
}
