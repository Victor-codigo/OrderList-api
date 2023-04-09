<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserGetGroups;

use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Group\Domain\Model\GROUP_TYPE;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupGetData\Dto\GroupGetDataDto;
use Group\Domain\Service\GroupGetData\GroupGetDataService;
use Group\Domain\Service\GroupUserGetGroups\Dto\GroupUserGeGroupsOutputDto;
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
    public function __invoke(GroupUserGetGroupsDto $input): GroupUserGeGroupsOutputDto
    {
        $userGroups = $this->userGroupRepository->findUserGroupsById($input->userId, null, GROUP_TYPE::GROUP);

        return $this->getUserGroups($userGroups, $input->page, $input->pageItems);
    }

    /**
     * @throws DBNotFoundException
     */
    private function getUserGroups(PaginatorInterface $userGroups, PaginatorPage $page, PaginatorPageItems $pageItems): GroupUserGeGroupsOutputDto
    {
        $userGroups->setPagination($page->getValue(), $pageItems->getValue());

        $groupsId = array_map(
            fn (UserGroup $userGroup) => $userGroup->getGroupId(),
            iterator_to_array($userGroups)
        );

        $groups = $this->groupGetDataService->__invoke(
            $this->createGroupGetDataDto($groupsId)
        );

        return new GroupUserGeGroupsOutputDto($page, $userGroups->getPagesTotal(), $groups);
    }

    /**
     * @param Identifier[] $groupsId
     */
    private function createGroupGetDataDto(array $groupsId): GroupGetDataDto
    {
        return new GroupGetDataDto($groupsId, GROUP_TYPE::GROUP);
    }
}
