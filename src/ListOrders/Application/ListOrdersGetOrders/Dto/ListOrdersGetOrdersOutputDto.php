<?php

declare(strict_types=1);

namespace ListOrders\Application\ListOrdersGetOrders\Dto;

use Common\Domain\Application\ApplicationOutputInterface;

class ListOrdersGetOrdersOutputDto implements ApplicationOutputInterface
{
    public function __construct(
        public readonly array $listOrderOrdersData,
        public readonly int $paginationCurrentPage,
        public readonly int $paginationTotalPages
    ) {
    }

    public function toArray(): array
    {
        return [
            'page' => $this->paginationCurrentPage,
            'pages_total' => $this->paginationTotalPages,
            'orders' => $this->listOrderOrdersData,
        ];
    }
}
