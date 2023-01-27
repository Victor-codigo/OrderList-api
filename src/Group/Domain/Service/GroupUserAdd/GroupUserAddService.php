<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserAdd;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupUserAdd\Dto\GroupUserAddDto;

class GroupUserAddService
{
    public function __construct(
        private UserGroupRepositoryInterface $userGroupRepository,
        private GroupRepositoryInterface $groupRepository
    ) {
    }

    /**
     * @return UserGroup[]
     *
     * @throws DBNotFoundException
     * @throws DBConnectionException
     */
    public function __invoke(GroupUserAddDto $input): array
    {
        $group = $this->groupRepository->findGroupByIdOrFail($input->groupId);
        $groupUsers = $this->userGroupRepository->findGroupUsersOrFail($input->groupId);
        $groupUsersNew = $this->getUsersNotInGroup($groupUsers, $input->usersId);
        $usersGroupNewObjects = $this->createUserGroup($input->groupId, $groupUsersNew, $input->rol, $group);

        $this->userGroupRepository->save($usersGroupNewObjects);

        return $usersGroupNewObjects;
    }

    /**
     * @param UserGroup[]  $usersGroup
     * @param Identifier[] $usersId
     *
     * @return Identifier[]
     */
    private function getUsersNotInGroup(array $usersGroup, array $usersId): array
    {
        $usersGroupIds = array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId(),
            $usersGroup
        );

        return array_diff($usersId, $usersGroupIds);
    }

    /**
     * @param Identifier[] $usersId
     *
     * @return UserGroup[]
     */
    private function createUserGroup(Identifier $groupId, array $usersId, Rol $rol, Group $group): array
    {
        $usersGroup = [];
        foreach ($usersId as $userId) {
            $usersGroup[] = new UserGroup(
                $groupId,
                $userId,
                ValueObjectFactory::createRoles([$rol]),
                $group
            );
        }

        return $usersGroup;
    }
}
