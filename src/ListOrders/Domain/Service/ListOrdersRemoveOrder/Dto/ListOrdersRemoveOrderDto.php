<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersRemoveOrder\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ListOrdersRemoveOrderDto
{
    /**
     * @param Identifier[] $ordersId
     */
    public function __construct(
        public readonly Identifier $listOrdersId,
        public readonly Identifier $groupId,
        public readonly array $ordersId
    ) {
    }
}
