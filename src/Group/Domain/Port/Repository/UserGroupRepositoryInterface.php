<?php

declare(strict_types=1);

namespace Group\Domain\Port\Repository;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Repository\RepositoryInterface;
use Group\Domain\Model\GROUP_ROLES;

interface UserGroupRepositoryInterface extends RepositoryInterface
{
    /**
     * @return UserGroup[]
     *
     * @throws DBNotFoundException
     */
    public function findGroupUsersOrFail(Identifier $groupId, int $limit = null, int $offset = null): array;

    /**
     * @param Identifier[] $usersId
     *
     * @return UserGroup[]
     *
     * @throws DBNotFoundException
     */
    public function findGroupUsersByUserIdOrFail(Identifier $groupId, array $usersId): array;

    /**
     * @return UserGroup[]
     *
     * @throws DBNotFoundException
     */
    public function findGroupUsersByRol(Identifier $groupId, GROUP_ROLES $groupRol): array;

    /**
     * @throws DBNotFoundException
     */
    public function findUserGroupsById(Identifier $userId, GROUP_ROLES|null $groupRol = null): array;

    /**
     * @throws DBNotFoundException
     */
    public function findGroupUsersNumberOrFail(Identifier $groupId): int;

    /**
     * @param UserGroup[] $usersGroup
     *
     * @throws DBConnectionException
     */
    public function save(array $usersGroup): void;

    /**
     * @param UserGroup[] $usersGroup
     */
    public function removeUsers(array $usersGroup): void;
}
