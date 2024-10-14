<?php

declare(strict_types=1);

namespace Group\Domain\Port\Repository;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\NameWithSpaces;
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
     * @param Group[] $groups
     *
     * @throws DBConnectionException
     */
    public function remove(array $groups): void;

    /**
     * @param Identifier[] $groupsId
     *
     * @return Group[]
     *
     * @throws DBNotFoundException
     */
    public function findGroupsByIdOrFail(array $groupsId): array;

    /**
     * @throws DBNotFoundException
     */
    public function findGroupByNameOrFail(NameWithSpaces $groupName): Group;
}
