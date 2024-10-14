<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupRemoveAllUserGroups;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupRemoveAllUserGroups\Dto\GroupRemoveAllUserGroupsOutputDto;

class GroupRemoveUserFromGroupsService
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        private UserGroupRepositoryInterface $userGroupRepository,
    ) {
    }

    /**
     * @param Group[]     $groups
     * @param UserGroup[] $usersGroups
     *
     * @throws DBNotFoundException
     */
    public function __invoke(array $groups, array $usersGroups): GroupRemoveAllUserGroupsOutputDto
    {
        $usersGroupsIndexGroupId = $this->getUsersGroupsIndexedByGroupId($usersGroups);
        $groupsId = $this->getGroupsId($usersGroups);
        $groupsUsersNumber = $this->getGroupsUsersNumber($groupsId);
        $groupsIdToRemove = $this->getGroupsToRemove($groups, $usersGroupsIndexGroupId, $groupsUsersNumber);
        $groupsIdToRemoveUser = $this->getGroupsToRemoveUser($groups, $groupsIdToRemove);

        $groupsRemoved = $this->removeGroups($groups, $groupsIdToRemove);
        $usersGroupRemoved = $this->removeGroupsUsers($groupsIdToRemoveUser, $usersGroupsIndexGroupId);

        return $this->createGroupRemoveAllUserGroupsOutputDto($groupsRemoved, $usersGroupRemoved['usersGroupsRemoved'], $usersGroupRemoved['usersGroupsSetAsAdmin'], $groups);
    }

    /**
     * @param UserGroup[] $usersGroups
     *
     * @return UserGroup[]
     */
    private function getUsersGroupsIndexedByGroupId(array $usersGroups): array
    {
        $usersGroupsIndexGroupId = [];
        foreach ($usersGroups as $userGroup) {
            $usersGroupsIndexGroupId[$userGroup->getGroupId()->getValue()] = $userGroup;
        }

        return $usersGroupsIndexGroupId;
    }

    /**
     * @param Identifier[] $groupsId
     *
     * @return array<string, int>
     *
     * @throws DBNotFoundException
     */
    private function getGroupsUsersNumber(array $groupsId): array
    {
        /**
         * @var array<int, array{
         *  groupId: string,
         *  groupUsers: int
         * }>
         */
        $groupsUsersNumPaginator = $this->userGroupRepository->findGroupsUsersNumberOrFail($groupsId);

        return array_combine(
            array_map(
                fn (array $groupNumber) => $groupNumber['groupId'],
                iterator_to_array($groupsUsersNumPaginator),
            ),
            array_map(
                fn (array $groupNumber) => $groupNumber['groupUsers'],
                iterator_to_array($groupsUsersNumPaginator),
            )
        );
    }

    /**
     * @param Group[]            $groups
     * @param UserGroup[]        $usersGroupsIndexedByGroupId
     * @param array<string, int> $groupsUsersNumber
     *
     * @return Identifier[]
     */
    private function getGroupsToRemove(array $groups, array $usersGroupsIndexedByGroupId, array $groupsUsersNumber): array
    {
        $adminRole = ValueObjectFactory::createRol(GROUP_ROLES::ADMIN);
        $groupsIdToRemove = [];
        foreach ($groups as $group) {
            $groupId = $group->getId();
            $userGroup = $usersGroupsIndexedByGroupId[$groupId->getValue()];

            if (!$userGroup->getRoles()->has($adminRole)) {
                continue;
            }

            if ($groupsUsersNumber[$groupId->getValue()] > 1) {
                continue;
            }

            $groupsIdToRemove[] = $groupId;
        }

        return $groupsIdToRemove;
    }

    /**
     * @param Group[]      $groups
     * @param Identifier[] $groupsIdToRemove
     *
     * @return Identifier[]
     */
    private function getGroupsToRemoveUser(array $groups, array $groupsIdToRemove): array
    {
        $groupsIdToRemoveString = array_map(
            fn (Identifier $groupIdToRemove): ?string => $groupIdToRemove->getValue(),
            $groupsIdToRemove
        );

        $groupsNotToRemove = array_filter(
            $groups,
            fn (Group $group): bool => !in_array($group->getId()->getValue(), $groupsIdToRemoveString)
        );

        return array_map(
            fn (Group $group): Identifier => $group->getId(),
            $groupsNotToRemove
        );
    }

    /**
     * @param Group[]      $groups
     * @param Identifier[] $groupsIdToRemove
     *
     * @return Group[] Groups removed
     *
     * @throws DBConnectionException
     */
    private function removeGroups(array $groups, array $groupsIdToRemove): array
    {
        $groupsIdToRemoveString = array_map(
            fn (Identifier $groupIdToRemove): ?string => $groupIdToRemove->getValue(),
            $groupsIdToRemove
        );

        $groupsToRemove = array_filter(
            $groups,
            fn (Group $group): bool => in_array($group->getId()->getValue(), $groupsIdToRemoveString)
        );

        $this->groupRepository->remove($groupsToRemove);

        return $groupsToRemove;
    }

    /**
     * @param Identifier[] $groupsIdToRemove
     * @param UserGroup[]  $usersGroupsIndexedByGroupId
     *
     * @return array{usersGroupsRemoved: UserGroup[], usersGroupsSetAsAdmin: UserGroup[]}
     *
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    private function removeGroupsUsers(array $groupsIdToRemove, array $usersGroupsIndexedByGroupId): array
    {
        $groupsIdToRemoveString = array_map(
            fn (Identifier $groupId): ?string => $groupId->getValue(),
            $groupsIdToRemove
        );

        $userGroupsToRemoveUser = array_filter(
            $usersGroupsIndexedByGroupId,
            fn (UserGroup $userGroup): bool => in_array($userGroup->getGroupId()->getValue(), $groupsIdToRemoveString)
        );

        $this->userGroupRepository->removeUsers($userGroupsToRemoveUser);
        $usersGroupsSetAsAdmin = $this->changeAdmin($groupsIdToRemove, $usersGroupsIndexedByGroupId);

        return [
            'usersGroupsRemoved' => $userGroupsToRemoveUser,
            'usersGroupsSetAsAdmin' => $usersGroupsSetAsAdmin,
        ];
    }

    /**
     * @param Identifier[] $groupsIdToRemove
     * @param UserGroup[]  $usersGroupsIndexedByGroupId
     *
     * @return UserGroup[] users set as admin
     *
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    private function changeAdmin(array $groupsIdToRemove, array $usersGroupsIndexedByGroupId): array
    {
        $adminRole = ValueObjectFactory::createRol(GROUP_ROLES::ADMIN);
        $groupsIdToRemoveString = array_map(
            fn (Identifier $groupId): ?string => $groupId->getValue(),
            $groupsIdToRemove
        );
        $userGroupToChangeAdmin = array_filter(
            $usersGroupsIndexedByGroupId,
            fn (UserGroup $userGroup): bool => $userGroup->getRoles()->has($adminRole)
                                      && in_array($userGroup->getGroupId()->getValue(), $groupsIdToRemoveString)
        );
        $groupsId = $this->getGroupsId($userGroupToChangeAdmin);

        try {
            $groupFirstUser = $this->userGroupRepository->findGroupsFirstUserByRolOrFail($groupsId, GROUP_ROLES::USER);

            foreach ($groupFirstUser as $userGroup) {
                $userGroup->setRoles(ValueObjectFactory::createRoles([$adminRole]));
            }

            $this->userGroupRepository->save($groupFirstUser);

            return $groupFirstUser;
        } catch (DBNotFoundException) {
            return [];
        }
    }

    /**
     * @param UserGroup[]|Group[] $usersGroups
     *
     * @return Identifier[]
     */
    private function getGroupsId(array $usersGroups): array
    {
        return array_map(
            fn (UserGroup $userGroup): Identifier => $userGroup->getGroupId(),
            $usersGroups
        );
    }

    /**
     * @param Group[]     $groupsRemoved
     * @param UserGroup[] $usersGroupsRemoved
     * @param UserGroup[] $userGroupsSetAsAdmin
     * @param Group[]     $groups
     */
    private function createGroupRemoveAllUserGroupsOutputDto(array $groupsRemoved, array $usersGroupsRemoved, array $userGroupsSetAsAdmin, array $groups): GroupRemoveAllUserGroupsOutputDto
    {
        return new GroupRemoveAllUserGroupsOutputDto($groupsRemoved, array_values($usersGroupsRemoved), $userGroupsSetAsAdmin, $groups);
    }
}
