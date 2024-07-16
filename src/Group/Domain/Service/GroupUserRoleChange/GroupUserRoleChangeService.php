<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserRoleChange;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupUserRoleChange\Dto\GroupUserRoleChangeDto;
use Group\Domain\Service\GroupUserRoleChange\Exception\GroupWithoutAdminsException;

class GroupUserRoleChangeService
{
    public function __construct(
        private UserGroupRepositoryInterface $userGroupRepository
    ) {
    }

    /**
     * @return Identifier[]
     *
     * @throws DBUniqueConstraintException
     * @throws DBNotFoundException
     * @throws DBConnectionException
     * @throws GroupWithoutAdminsException
     */
    public function __invoke(GroupUserRoleChangeDto $input): array
    {
        $groupUsersPaginator = $this->userGroupRepository->findGroupUsersOrFail($input->groupId);
        $groupUsers = iterator_to_array($groupUsersPaginator);
        $groupUsersValid = $this->getUsersValid($groupUsers, $input->usersId);

        if (empty($groupUsersValid)) {
            return [];
        }

        $this->setUsersGroupRol($groupUsers, $groupUsersValid, $input->rol);
        $this->userGroupRepository->save($groupUsersValid);

        return array_map(
            fn (UserGroup $userGroup): Identifier => $userGroup->getUserId(),
            $groupUsersValid
        );
    }

    /**
     * @param Identifier[] $usersId
     * @param UserGroup[]  $groupUsers
     *
     * @return UserGroup[]
     */
    private function getUsersValid(array $groupUsers, array $usersId): array
    {
        return array_values(array_filter(
            $groupUsers,
            fn (UserGroup $userGroup): bool => in_array($userGroup->getUserId(), $usersId)
        ));
    }

    /**
     * @param UserGroup[] $usersGroup
     *
     * @throws GroupWithoutAdminsException
     */
    private function setUsersGroupRol(array $usersGroup, array $usersToChangeRol, Rol $rol): void
    {
        foreach ($usersToChangeRol as $userGroupToChangeRol) {
            $userGroupToChangeRol->setRoles(ValueObjectFactory::createRoles([$rol]));
        }

        $groupAdmins = array_filter(
            $usersGroup,
            fn (UserGroup $userGroup): bool => $userGroup->getRoles()->has(ValueObjectFactory::createRol(GROUP_ROLES::ADMIN))
        );

        if (empty($groupAdmins)) {
            throw GroupWithoutAdminsException::fromMessage('Cannot change roles, group witout admims');
        }
    }
}
