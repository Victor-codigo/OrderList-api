<?php

declare(strict_types=1);

namespace Group\Domain\Port\Repository;

use Common\Domain\Model\ValueObject\String\Name;
use Common\Domain\Ports\Repository\RepositoryInterface;
use Group\Domain\Model\Group;

interface GroupRepositoryInterface extends RepositoryInterface
{
    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function save(Group $group): void;

    /**
     * @throws DBConnectionException
     */
    public function remove(Group $group): void;

    /**
     * @return Group[]
     *
     * @throws DBNotFoundException
     */
    public function findGroupsByIdOrFail(array $id): array;

    /**
     * @throws DBNotFoundException
     */
    public function findGroupByNameOrFail(Name $groupName): Group;
}
