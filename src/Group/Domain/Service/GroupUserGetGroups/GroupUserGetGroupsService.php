<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserGetGroups;

use Common\Domain\Model\ValueObject\String\Identifier;
use Group\Domain\Model\GROUP_TYPE;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupGetData\Dto\GroupGetDataDto;
use Group\Domain\Service\GroupGetData\GroupGetDataService;
use Group\Domain\Service\GroupUserGetGroups\Dto\GroupUserGetGroupsDto;

class GroupUserGetGroupsService
{
    public function __construct(
        private UserGroupRepositoryInterface $userGroupRepository,
        private GroupGetDataService $groupGetDataService,
    ) {
    }

    /**
     * @throws DBNotFoundException
     */
    public function __invoke(GroupUserGetGroupsDto $input): \Generator
    {
        $userGroups = $this->userGroupRepository->findUserGroupsById($input->userId);

        return $this->getUserGroups($userGroups);
    }

    /**
     * @param UserGroup[] $userGroups
     *
     * @throws DBNotFoundException
     */
    private function getUserGroups(array $userGroups): \Generator
    {
        $groupsId = array_map(
            fn (UserGroup $userGroup) => $userGroup->getGroupId(),
            $userGroups
        );

        return $this->groupGetDataService->__invoke(
            $this->createGroupGetDataDto($groupsId)
        );
    }

    /**
     * @param Identifier[] $groupsId
     */
    private function createGroupGetDataDto(array $groupsId): GroupGetDataDto
    {
        return new GroupGetDataDto($groupsId, GROUP_TYPE::GROUP);
    }
}
