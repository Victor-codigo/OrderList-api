<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserAdd;

use Common\Domain\Config\AppConfig;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupUserAdd\Dto\GroupUserAddDto;
use Group\Domain\Service\GroupUserAdd\Exception\GroupAddUsersMaxNumberExceededException;

class GroupUserAddService
{
    private const GROUP_USERS_MAX = AppConfig::GROUP_USERS_MAX;

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
     * @throws GroupAddUsersMaxNumberExceededException
     */
    public function __invoke(GroupUserAddDto $input): array
    {
        $this->validateGroupUsersNumber($input->groupId, count($input->usersId));
        $group = $this->groupRepository->findGroupsByIdOrFail([$input->groupId]);
        $groupUsers = $this->userGroupRepository->findGroupUsersOrFail($input->groupId);
        $groupUsersNew = $this->getUsersNotInGroup($groupUsers, $input->usersId);
        $usersGroupNewObjects = $this->createUserGroup($input->groupId, $groupUsersNew, $input->rol, $group[0]);

        $this->userGroupRepository->save($usersGroupNewObjects);

        return $usersGroupNewObjects;
    }

    /**
     * @param Identifier[] $usersId
     *
     * @return Identifier[]
     */
    private function getUsersNotInGroup(PaginatorInterface $usersGroup, array $usersId): array
    {
        $usersGroupIds = array_map(
            fn (UserGroup $userGroup) => $userGroup->getUserId(),
            iterator_to_array($usersGroup)
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

    /**
     * @throws DBNotFoundException
     * @throws GroupAddUsersMaxNumberExceededException
     */
    private function validateGroupUsersNumber(Identifier $groupId, int $numUsersToAdd): void
    {
        $groupUsersNumber = $this->userGroupRepository->findGroupUsersNumberOrFail($groupId);

        if (self::GROUP_USERS_MAX < $groupUsersNumber + $numUsersToAdd) {
            throw GroupAddUsersMaxNumberExceededException::fromMessage("Group [{$groupId}] maximum user number overpass");
        }
    }
}
