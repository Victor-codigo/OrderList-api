<?php

declare(strict_types=1);

namespace Order\Application\OrderBought\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Order\Domain\Model\Order;

class OrderBoughtOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly Order $order
    ) {
    }

    #[\Override]
    public function toArray(): array
    {
        return [
            'id' => $this->order->getId()->getValue(),
        ];
    }
}
