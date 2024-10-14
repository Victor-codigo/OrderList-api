<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupUserGetGroups;

use Common\Domain\Model\ValueObject\Integer\PaginatorPage;
use Common\Domain\Model\ValueObject\Integer\PaginatorPageItems;
use Common\Domain\Model\ValueObject\Object\Rol;
use Common\Domain\Model\ValueObject\String\Identifier;
use Common\Domain\Model\ValueObject\String\Path;
use Common\Domain\Ports\Paginator\PaginatorInterface;
use Common\Domain\Validation\Group\GROUP_ROLES;
use Common\Domain\Validation\Group\GROUP_TYPE;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupGetData\Dto\GroupGetDataDto;
use Group\Domain\Service\GroupGetData\GroupGetDataService;
use Group\Domain\Service\GroupUserGetGroups\Dto\GroupUserGetGroupsDto;
use Group\Domain\Service\GroupUserGetGroups\Dto\GroupUserGetGroupsOutputDto;

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
    public function __invoke(GroupUserGetGroupsDto $input): GroupUserGetGroupsOutputDto
    {
        $userGroups = $this->userGroupRepository->findUserGroupsByName($input->userId, $input->filterText, $input->groupType, $input->orderAsc);

        return $this->getUserGroups($userGroups, $input->groupType, $input->page, $input->pageItems, $input->userImage);
    }

    /**
     * @param PaginatorInterface<int, UserGroup> $userGroups
     *
     * @throws DBNotFoundException
     */
    private function getUserGroups(PaginatorInterface $userGroups, ?GROUP_TYPE $groupType, PaginatorPage $page, PaginatorPageItems $pageItems, Path $userImage): GroupUserGetGroupsOutputDto
    {
        $userGroups->setPagination($page->getValue(), $pageItems->getValue());

        $groupsId = array_map(
            fn (UserGroup $userGroup): Identifier => $userGroup->getGroupId(),
            iterator_to_array($userGroups)
        );

        $groupsIsAdmin = array_filter(
            iterator_to_array($userGroups),
            fn (UserGroup $userGroup): bool => $userGroup->getRoles()->has(new Rol(GROUP_ROLES::ADMIN))
        );

        $groupsIsAdminId = array_map(
            fn (UserGroup $userGroup): ?string => $userGroup->getGroupId()->getValue(),
            $groupsIsAdmin
        );

        /**
         * @var array<int, array{
         *  group_id: string|null,
         *  type: string,
         *  name: string|null,
         *  description: string|null,
         *  image: string|null,
         *  created_on: string,
         *  admin: bool
         * }> $groupsData
         */
        $groupsData = iterator_to_array($this->groupGetDataService->__invoke(
            $this->createGroupGetDataDto($groupsId, $groupType, $userImage)
        ));

        array_walk(
            $groupsData,
            fn (array &$groupData): bool => $groupData['admin'] = in_array($groupData['group_id'], $groupsIsAdminId)
        );

        return new GroupUserGetGroupsOutputDto($page, $userGroups->getPagesTotal(), $groupsData);
    }

    /**
     * @param Identifier[] $groupsId
     */
    private function createGroupGetDataDto(array $groupsId, ?GROUP_TYPE $groupType, Path $userImage): GroupGetDataDto
    {
        return new GroupGetDataDto($groupsId, $groupType, $userImage);
    }
}
