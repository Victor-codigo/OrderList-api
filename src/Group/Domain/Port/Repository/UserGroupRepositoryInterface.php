<?php

declare(strict_types=1);

namespace Group\Domain\Port\Repository;

use Common\Domain\Model\ValueObject\Group\Filter;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Ports\Repository\RepositoryInterface;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\UserGroup;

interface UserGroupRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Identifier[] $groupsId
     *
     * @return PaginatorInterface<int, UserGroup>
     *
     * @throws DBNotFoundException
     */
    public function findGroupsUsersOrFail(array $groupsId, GROUP_ROLES $groupRole): PaginatorInterface;

    /**
     * @return PaginatorInterface<int, UserGroup>
     *
     * @throws DBNotFoundException
     */
    public function findGroupUsersOrFail(Identifier $groupId): PaginatorInterface;

    /**
     * @param Identifier[] $groupsId
     *
     * @return UserGroup[]
     *
     * @throws DBNotFoundException
     */
    public function findGroupsFirstUserByRolOrFail(array $groupsId, GROUP_ROLES $groupRole): array;

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
     * @return PaginatorInterface<int, UserGroup>
     *
     * @throws DBNotFoundException
     */
    public function findUserGroupsById(Identifier $userId, ?GROUP_ROLES $groupRol = null, ?GROUP_TYPE $groupType = null): PaginatorInterface;

    /**
     * @return PaginatorInterface<int, UserGroup>
     *
     * @throws DBNotFoundException
     */
    public function findUserGroupsByName(Identifier $userId, ?Filter $filterText, ?GROUP_TYPE $groupType, bool $orderAsc): PaginatorInterface;

    /**
     * @throws DBNotFoundException
     */
    public function findGroupUsersNumberOrFail(Identifier $groupId): int;

    /**
     * @param Identifier[] $groupsId
     *
     * @return PaginatorInterface<int, array{
     *  groupId: string,
     *  groupUsers: int
     * }>
     *
     * @throws DBNotFoundException
     */
    public function findGroupsUsersNumberOrFail(array $groupsId): PaginatorInterface;

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
