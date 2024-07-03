<?php

declare(strict_types=1);

namespace User\Adapter\Database\Orm\Doctrine\Repository;

use Override;
use Common\Adapter\Database\Orm\Doctrine\Repository\RepositoryBase;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Doctrine\Persistence\ManagerRegistry;
use User\Domain\Model\Profile;
use User\Domain\Port\Repository\ProfileRepositoryInterface;

class ProfileRepository extends RepositoryBase implements ProfileRepositoryInterface
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Profile::class);
    }

    /**
     * @param Identifier $id
     *
     * @return Profile[]
     */
    #[Override]
    public function findProfilesOrFail(array $id): array
    {
        $profiles = $this->findBy(['id' => $id]);

        if (empty($profiles)) {
            throw DBNotFoundException::fromMessage('Not profiles were found');
        }

        return $profiles;
    }
}
