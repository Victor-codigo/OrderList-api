<?php

declare(strict_types=1);

namespace Order\Domain\Service\OrderRemoveAllGroupsOrders\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class OrderRemoveAllGroupsOrdersOutputDto
{
    /**
     * @param Identifier[] $ordersIdRemoved
     * @param Identifier[] $ordersIdChangedUserId
     */
    public function __construct(
        public readonly array $ordersIdRemoved,
        public readonly array $ordersIdChangedUserId,
    ) {
    }
}
