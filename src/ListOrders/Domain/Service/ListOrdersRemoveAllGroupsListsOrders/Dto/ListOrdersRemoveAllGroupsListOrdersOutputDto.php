<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ListOrdersRemoveAllGroupsListOrdersOutputDto
{
    /**
     * @param Identifier[] $listsOrdersIdRemoved
     * @param Identifier[] $listsOrdersIdChangedUserId
     */
    public function __construct(
        public readonly array $listsOrdersIdRemoved,
        public readonly array $listsOrdersIdChangedUserId,
    ) {
    }
}
