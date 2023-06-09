<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersRemoveOrder\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use ListOrders\Domain\Model\ListOrdersOrders;

class ListOrdersRemoveOrderOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly array $listOrdersOrders
    ) {
    }

    public function toArray(): array
    {
        $ordersRemovedId = array_map(
            fn (ListOrdersOrders $listOrdersOrders) => $listOrdersOrders->getOrderId()->getValue(),
            $this->listOrdersOrders
        );

        return ['id' => $ordersRemovedId];
    }
}
