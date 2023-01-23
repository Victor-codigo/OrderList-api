<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupCreate;

use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\ValueObjectFactory;
use Group\Domain\Model\GROUP_ROLES;
use Group\Domain\Model\GROUP_TYPE;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Service\GroupCreate\Dto\GroupCreateDto;

class GroupCreateService
{
    public function __construct(
        private GroupRepositoryInterface $groupRepository
    ) {
    }

    /**
     * @throws DBUniqueConstraintException
     * @throws DBConnectionException
     */
    public function __invoke(GroupCreateDto $input): Group
    {
        $groupNew = $this->createGroup($input);
        $userGroup = $this->createUserGroup($groupNew->getId(), $input->userCreatorId, $groupNew);
        $groupNew->addUserGroup($userGroup);
        $this->groupRepository->save($groupNew);

        return $groupNew;
    }

    private function createGroup(GroupCreateDto $input): Group
    {
        $id = $this->groupRepository->generateId();

        return new Group(
            ValueObjectFactory::createIdentifier($id),
            $input->name,
            ValueObjectFactory::createGroupType(GROUP_TYPE::USER),
            $input->description
        );
    }

    private function createUserGroup(Identifier $groupId, Identifier $userId, Group $group): UserGroup
    {
        $id = $this->groupRepository->generateId();

        return new UserGroup(
            ValueObjectFactory::createIdentifier($id),
            $groupId,
            $userId,
            ValueObjectFactory::createRoles([ValueObjectFactory::createRol(GROUP_ROLES::ADMIN)]),
            $group
        );
    }
}
