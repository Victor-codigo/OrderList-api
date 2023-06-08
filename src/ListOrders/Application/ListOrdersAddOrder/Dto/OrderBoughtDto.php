<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersAddOrder\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class OrderBoughtDto
{
    public function __construct(
        public readonly Identifier $orderId,
        public readonly bool $bought
    ) {
    }
}
