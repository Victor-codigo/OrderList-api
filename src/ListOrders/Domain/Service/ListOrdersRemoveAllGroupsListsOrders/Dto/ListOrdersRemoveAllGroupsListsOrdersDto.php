<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ListOrdersRemoveAllGroupsListsOrdersDto
{
    /**
     * @param Identifier[] $groupsIdToRemoveListsOrders
     * @param array{}|array<int, array{
     *  group_id: Identifier,
     *  admin: Identifier
     * }> $groupsIdToChangeListsOrdersUser
     */
    public function __construct(
        public readonly array $groupsIdToRemoveListsOrders,
        public readonly array $groupsIdToChangeListsOrdersUser,
    ) {
    }
}
