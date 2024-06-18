<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersRemoveAllGroupsListsOrders\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ListOrdersRemoveAllGroupsListOrdersOutputDto
{
    /**
     * @param Identifier[] $listOrdersIdRemoved
     * @param Identifier[] $listOrdersIdChangedUserId
     */
    public function __construct(
        public readonly array $listOrdersIdRemoved,
        public readonly array $listOrdersIdChangedUserId,
    ) {
    }
}
