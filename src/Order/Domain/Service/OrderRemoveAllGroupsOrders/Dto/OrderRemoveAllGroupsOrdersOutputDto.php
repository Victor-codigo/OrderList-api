<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderRemoveAllGroupsOrders\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class OrderRemoveAllGroupsOrdersOutputDto
{
    /**
     * @param Identifier[]                                               $ordersIdRemoved
     * @param array<int, array{group_id: Identifier, admin: Identifier}> $ordersIdChangedUserId
     */
    public function __construct(
        public readonly array $ordersIdRemoved,
        public readonly array $ordersIdChangedUserId,
    ) {
    }
}
