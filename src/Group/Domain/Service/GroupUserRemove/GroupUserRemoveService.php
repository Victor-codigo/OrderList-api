<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserRemove;

use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupUserRemove\Dto\GroupUserRemoveDto;
use Group\Domain\Service\GroupUserRemove\Exception\GroupUserRemoveEmptyException;
use Group\Domain\Service\GroupUserRemove\Exception\GroupUserRemoveGroupWithoutAdminException;
use Group\Domain\Service\GroupUserRemove\Exception\GroupUserRemovePermissionsException;

class GroupUserRemoveService
{
    public function __construct(
        private UserGroupRepositoryInterface $userGroupRepository,
        private GroupRepositoryInterface $groupRepository
    ) {
    }

    /**
     * @return Identifier[] Users removed from the group
     *
     * @throws GroupUserRemoveEmptyException
     * @throws GroupUserRemoveGroupWithoutAdminException
     * @throws DBNotFoundException
     * @throws GroupUserRemovePermissionsException
     */
    public function __invoke(GroupUserRemoveDto $input): array
    {
        $group = $this->groupRepository->findGroupsByIdOrFail([$input->groupId]);
        $this->isGroupUserRemovable($group[0]);

        $usersGroup = $this->userGroupRepository->findGroupUsersByUserIdOrFail($input->groupId, $input->usersId);

        $this->validateNotToRemoveAllUsersOfTheGroup($input->groupId, $usersGroup);
        $this->validateNoRemoveAllAdminsFromTheGroup($input->groupId, $usersGroup);

        $this->userGroupRepository->removeUsers($usersGroup);

        return array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId(),
            $usersGroup
        );
    }

    /**
     * @throws GroupUserRemovePermissionsException
     */
    private function isGroupUserRemovable(Group $group): void
    {
        if (GROUP_TYPE::USER === $group->getType()->getValue()) {
            throw new GroupUserRemovePermissionsException();
        }
    }

    /**
     * @throws GroupUserRemoveEmptyException
     */
    private function validateNotToRemoveAllUsersOfTheGroup(Identifier $groupId, array $usersGroupToRemove): void
    {
        $groupUsersNum = $this->userGroupRepository->findGroupUsersNumberOrFail($groupId);

        if ($groupUsersNum - count($usersGroupToRemove) <= 0) {
            throw GroupUserRemoveEmptyException::fromMessage('The group can not be empty');
        }
    }

    /**
     * @throws GroupUserRemoveGroupWithoutAdminException
     */
    private function validateNoRemoveAllAdminsFromTheGroup(Identifier $groupId, array $usersGroupToRemove): void
    {
        $groupAdmins = $this->userGroupRepository->findGroupUsersByRol($groupId, GROUP_ROLES::ADMIN);
        $usersGroupToRemoveIds = array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId()->getValue(),
            $usersGroupToRemove
        );
        $groupAdminsRest = array_filter(
            $groupAdmins,
            fn (UserGroup $userGroup) => !in_array($userGroup->getUserId()->getValue(), $usersGroupToRemoveIds)
        );

        if (empty($groupAdminsRest)) {
            throw GroupUserRemoveGroupWithoutAdminException::fromMessage('Can not be a group witout an admin');
        }
    }
}
