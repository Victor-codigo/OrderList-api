<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersAddOrder\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;
use ListOrders\Application\ListOrdersAddOrder\Dto\OrderBoughtDto;

class ListOrdersAddOrderDto
{
    /**
     * @param OrderBoughtDto[] $ordersBought
     */
    public function __construct(
        public readonly Identifier $listOrdersId,
        public readonly Identifier $groupId,
        public readonly array $ordersBought
    ) {
    }
}
