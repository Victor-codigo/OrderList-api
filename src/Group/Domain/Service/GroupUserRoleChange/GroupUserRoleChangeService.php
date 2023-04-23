<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserRoleChange;

use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
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
        $groupUsers = $this->userGroupRepository->findGroupUsersOrFail($input->groupId);
        $groupUsersValid = $this->getUsersValid($groupUsers, $input->usersId);

        if (empty($groupUsersValid)) {
            return [];
        }

        $this->setUsersGroupRol($groupUsersValid, $input->rol);
        $this->userGroupRepository->save($groupUsersValid);

        return array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId(),
            $groupUsersValid
        );
    }

    /**
     * @param Identifier[] $usersId
     *
     * @return UserGroup[]
     */
    private function getUsersValid(PaginatorInterface $groupUsers, array $usersId): array
    {
        return array_values(array_filter(
            iterator_to_array($groupUsers),
            fn (UserGroup $userGroup) => in_array($userGroup->getUserId(), $usersId)
        ));
    }

    /**
     * @param UserGroup[] $usersGroup
     *
     * @throws GroupWithoutAdminsException
     */
    private function setUsersGroupRol(array $usersGroup, Rol $rol): void
    {
        foreach ($usersGroup as $userGroup) {
            $userGroup->setRoles(ValueObjectFactory::createRoles([$rol]));
        }

        $groupAdmins = array_filter(
            $usersGroup,
            fn (UserGroup $userGroup) => $userGroup->getRoles()->has(ValueObjectFactory::createRol(GROUP_ROLES::ADMIN))
        );

        if (empty($groupAdmins)) {
            throw GroupWithoutAdminsException::fromMessage('Cannot change roles, group witout admims');
        }
    }
}
