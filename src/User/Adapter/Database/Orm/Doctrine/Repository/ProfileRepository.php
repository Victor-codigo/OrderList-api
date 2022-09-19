<?php

declare(strict_types=1);

namespace User\Adapter\Database\Orm\Doctrine\Repository;

use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Doctrine\Persistence\ManagerRegistry;
use User\Domain\Model\Profile;
use User\Domain\Port\Repository\ProfileRepositoryInterface;

class ProfileRepository extends RepositoryBase implements ProfileRepositoryInterface
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Profile::class);
    }

    public function save(Profile $profile): void
    {
        $this->objectManager->persist($profile);
        $this->objectManager->flush();
    }

    public function remove(Profile $profile): void
    {
        $this->objectManager->persist($profile);
        $this->objectManager->flush();
    }
}
