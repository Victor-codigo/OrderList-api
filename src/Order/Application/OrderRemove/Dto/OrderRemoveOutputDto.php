<?php

declare(strict_types=1);

namespace Order\Application\OrderRemove\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use Order\Domain\Model\Order;

class OrderRemoveOutputDto implements ApplicationOutputInterface
{
    /**
     * @param string[] $ordersId
     */
    public function __construct(
        public readonly array $ordersId
    ) {
    }

    #[\Override]
    public function toArray(): array
    {
        $ordersIdPlain = array_map(
            fn (Order $orderId): ?string => $orderId->getId()->getValue(),
            $this->ordersId
        );

        return [
            'id' => $ordersIdPlain,
        ];
    }
}
