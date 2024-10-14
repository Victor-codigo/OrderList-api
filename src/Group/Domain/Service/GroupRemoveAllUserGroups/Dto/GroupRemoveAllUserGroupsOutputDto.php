<?php

declare(strict_types=1);

namespace Group\Domain\Service\GroupRemoveAllUserGroups\Dto;

use Group\Domain\Model\Group;
use Group\Domain\Model\UserGroup;

class GroupRemoveAllUserGroupsOutputDto
{
    /**
     * @param Group[]     $groupsIdRemoved
     * @param UserGroup[] $usersIdGroupsRemoved
     * @param UserGroup[] $usersGroupsIdSetAsAdmin
     * @param Group[]     $groups
     */
    public function __construct(
        public readonly array $groupsIdRemoved,
        public readonly array $usersIdGroupsRemoved,
        public readonly array $usersGroupsIdSetAsAdmin,
        public readonly array $groups,
    ) {
    }
}
