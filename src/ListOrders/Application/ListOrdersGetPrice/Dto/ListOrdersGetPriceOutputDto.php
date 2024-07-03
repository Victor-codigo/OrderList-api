<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetPrice\Dto;

use Override;
use Common\Domain\Application\ApplicationOutputInterface;
use Common\Domain\Model\ValueObject\Float\Money;

class ListOrdersGetPriceOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        private Money $totalPrice,
        private Money $boughtPrice,
    ) {
    }

    #[Override]
    public function toArray(): array
    {
        return [
            'total' => $this->totalPrice->getValue(),
            'bought' => $this->boughtPrice->getValue(),
        ];
    }
}
