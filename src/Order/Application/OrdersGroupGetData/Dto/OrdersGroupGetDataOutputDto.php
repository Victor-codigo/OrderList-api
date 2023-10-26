<?php

declare(strict_types=1);

namespace Order\Application\OrdersGroupGetData\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

class OrdersGroupGetDataOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly array $ordersGroupData,
        public readonly int $page,
        public readonly int $pagesTotal,
    ) {
    }

    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'pages_total' => $this->pagesTotal,
            'orders' => $this->ordersGroupData,
        ];
    }
}
