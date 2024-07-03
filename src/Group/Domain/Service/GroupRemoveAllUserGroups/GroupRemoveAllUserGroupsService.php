<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupRemoveAllUserGroups;

use Common\Domain\Model\ValueObject\String\Identifier;
use Generator;
use Common\Domain\Database\Orm\Doctrine\Repository\Exception\DBNotFoundException;
use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;
use Group\Domain\Port\Repository\GroupRepositoryInterface;
use Group\Domain\Port\Repository\UserGroupRepositoryInterface;
use Group\Domain\Service\GroupRemoveAllUserGroups\Dto\GroupRemoveAllUserGroupsDto;

class GroupRemoveAllUserGroupsService
{
    private const int PAGINATION_PAGE_ITEMS = 100;

    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        private UserGroupRepositoryInterface $userGroupRepository,
        private GroupRemoveUserFromGroupsService $groupRemoveUserFromGroupsService
    ) {
    }

    /**
     * @return Generator<GroupRemoveAllUserGroupsOutputDto>
     *
     * @throws DBNotFoundException
     */
    public function __invoke(GroupRemoveAllUserGroupsDto $input): Generator
    {
        $groupsPaginator = $this->userGroupRepository->findUserGroupsById($input->userId, null, null);

        foreach ($groupsPaginator->getAllPages(self::PAGINATION_PAGE_ITEMS) as $usersGroupsIterator) {
            $usersGroups = iterator_to_array($usersGroupsIterator);
            $groups = $this->getGroups($usersGroups);

            yield $this->groupRemoveUserFromGroupsService->__invoke($groups, $usersGroups);
        }
    }

    /**
     * @param UserGroup[] $usersGroups
     *
     * @return Group[]
     */
    private function getGroups(array $usersGroups): array
    {
        if (empty($usersGroups)) {
            return [];
        }

        $groupsId = array_map(
            fn (UserGroup $userGroup): Identifier => $userGroup->getGroupId(),
            $usersGroups
        );

        return $this->groupRepository->findGroupsByIdOrFail($groupsId);
    }
}
