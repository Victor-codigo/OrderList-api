<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ListOrdersRemoveAllGroupsListOrdersOutputDto
{
    /**
     * @param Identifier[]                                               $listsOrdersIdRemoved
     * @param array<int, array{group_id: Identifier, admin: Identifier}> $listsOrdersIdChangedUserId
     */
    public function __construct(
        public readonly array $listsOrdersIdRemoved,
        public readonly array $listsOrdersIdChangedUserId,
    ) {
    }
}
