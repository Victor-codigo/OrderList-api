<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersGetPrice\Dto;

use Common\Domain\Model\ValueObject\String\Identifier;

class ListOrdersGetPriceDto
{
    public function __construct(
        public readonly Identifier $listOrdersId,
        public readonly Identifier $groupId,
    ) {
    }
}
