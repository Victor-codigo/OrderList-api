<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\Dto;

class ListOrdersRemoveAllGroupsListsOrdersDto
{
    public function __construct(
        public readonly array $groupsIdToRemoveListsOrders,
        public readonly array $groupsIdToChangeListsOrdersUser,
    ) {
    }
}
