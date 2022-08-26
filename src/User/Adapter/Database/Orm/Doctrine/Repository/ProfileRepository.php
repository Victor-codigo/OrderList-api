<?php

declare(strict_types=1);

namespace User\Adapter\Database\Orm\Doctrine\Repository;

use Doctrine\Persistence\ManagerRegistry;
use User\Adapter\Database\Orm\Doctrine\Entity\Profile;
use User\Domain\Model\EntityBase as EntityBaseDomain;

class ProfileRepository extends UserRepositoryBase
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Profile::class);
    }

    public function save(EntityBaseDomain $profile): void
    {
        $doctrineProfile = Profile::createFromDomain($profile);

        $this->objectManager->persist($doctrineProfile);
        $this->objectManager->flush();
    }

    public function remove(EntityBaseDomain $profile): void
    {
        $doctrineProfile = Profile::createFromDomain($profile);

        $this->objectManager->persist($doctrineProfile);
        $this->objectManager->flush();
    }
}
