<?php

declare(strict_types=1);

namespace ListOrders\Domain\Service\ListOrdersGetPrice\Dto;

use Common\Domain\Model\ValueObject\Float\Money;

class ListOrdersGetPriceOutputDto
{
    public function __construct(
        public readonly Money $totalPrice,
        public readonly Money $boughtPrice,
    ) {
    }
}
