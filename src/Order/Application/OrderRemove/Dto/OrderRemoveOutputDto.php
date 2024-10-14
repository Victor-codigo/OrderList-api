<?php

declare(strict_types=1);

namespace Order\Application\OrderRemove\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Order\Domain\Model\Order;

class OrderRemoveOutputDto implements ApplicationOutputInterface
{
    /**
     * @param Order[] $orders
     */
    public function __construct(
        public readonly array $orders,
    ) {
    }

    /**
     * @return array{ id: string[] }
     */
    #[\Override]
    public function toArray(): array
    {
        $ordersIdPlain = array_map(
            fn (Order $order): ?string => $order->getId()->getValue(),
            $this->orders
        );

        return [
            'id' => $ordersIdPlain,
        ];
    }
}
