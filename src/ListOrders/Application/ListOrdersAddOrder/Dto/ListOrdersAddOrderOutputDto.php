<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersAddOrder\Dto;

use Common\Domain\Application\ApplicationOutputInterface;
use ListOrders\Domain\Model\ListOrdersOrders;

class ListOrdersAddOrderOutputDto implements ApplicationOutputInterface
{
    /**
     * @param ListOrdersOrders[] $listOrders
     */
    public function __construct(
        public array $listOrders,
    ) {
    }

    public function toArray(): array
    {
        return array_map(
            fn (ListOrdersOrders $listOrdersOrders) => [
                'list_orders_id' => $listOrdersOrders->getListOrdersId()->getValue(),
                'order_id' => $listOrdersOrders->getOrderId()->getValue(),
                'bought' => $listOrdersOrders->getBought(),
            ],
            $this->listOrders
        );
    }
}
