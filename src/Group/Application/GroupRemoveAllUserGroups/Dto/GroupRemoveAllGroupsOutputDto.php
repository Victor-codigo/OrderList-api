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
     * @param array{}|array{
     *  group_id: Identifier,
     *  user_id: Identifier
     * } $groupsIdUserSetAsAdmin
     */
    public function __construct(
        public array $groupsIdRemoved,
        public array $groupsIdUserRemoved,
        public array $groupsIdUserSetAsAdmin,
    ) {
    }

    /**
     * @return array{}|array{
     *  groups_id_removed: string[],
     *  groups_id_user_removed: string[],
     *  groups_id_user_set_as_admin: array{
     *      group_id: string,
     *      user_id: string
     *  }[]
     * }
     */
    #[\Override]
    public function toArray(): array
    {
        return [
            'groups_id_removed' => array_map(
                fn (Identifier $groupId): ?string => $groupId->getValue(),
                $this->groupsIdRemoved
            ),
            'groups_id_user_removed' => array_map(
                fn (Identifier $groupId): ?string => $groupId->getValue(),
                $this->groupsIdUserRemoved
            ),
            'groups_id_user_set_as_admin' => array_map(
                // @phpstan-ignore argument.type
                fn (array $groupUserId): array => [
                    'group_id' => (string) $groupUserId['group_id']->getValue(),
                    'user_id' => (string) $groupUserId['user_id']->getValue(),
                ],
                $this->groupsIdUserSetAsAdmin
            ),
        ];
    }
}
