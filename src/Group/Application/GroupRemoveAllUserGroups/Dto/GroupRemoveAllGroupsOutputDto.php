<?php

declare(strict_types=1);

namespace Group\Application\GroupRemoveAllUserGroups\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\String\Identifier;

class GroupRemoveAllGroupsOutputDto implements ApplicationOutputInterface
{
    /**
     * @param Identifier[] $groupsIdRemoved
     * @param Identifier[] $groupsIdUserRemoved
     * @param Identifier[] $groupsIdUserSetAsAdmin
     */
    public function __construct(
        public array $groupsIdRemoved,
        public array $groupsIdUserRemoved,
        public array $groupsIdUserSetAsAdmin
    ) {
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return [
            'groups_id_removed' => array_map(
                fn (Identifier $groupId) => $groupId->getValue(),
                $this->groupsIdRemoved
            ),
            'groups_id_user_removed' => array_map(
                fn (Identifier $groupId) => $groupId->getValue(),
                $this->groupsIdUserRemoved
            ),
            'groups_id_user_set_as_admin' => array_map(
                fn (array $groupUserId) => [
                    'group_id' => $groupUserId['group_id']->getValue(),
                    'user_id' => $groupUserId['user_id']->getValue(),
                ],
                $this->groupsIdUserSetAsAdmin
            ),
        ];
    }
}
